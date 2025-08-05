<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;
use Twilio\Rest\Client;

class SmsTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        $templates = QueryBuilder::for(SmsTemplate::class)
            ->allowedFilters(['name', 'segment_type', 'is_active'])
            ->allowedSorts(['created_at', 'name'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => SmsTemplate::count(),
            'active' => SmsTemplate::where('is_active', true)->count(),
            'inactive' => SmsTemplate::where('is_active', false)->count(),
        ];

        return Inertia::render('SmsTemplates/Index', [
            'templates' => $templates,
            'stats' => $stats,
            'filters' => $request->only(['filter', 'sort'])
        ]);
    }

    public function create(): Response
    {
        $variables = [
            'contact' => ['business_name', 'phone', 'email', 'website', 'address', 'owner_name'],
            'campaign' => ['activity_type', 'city'],
            'rating' => ['google_rating', 'review_count'],
            'other' => ['current_date', 'current_time', 'sender_name', 'sender_company']
        ];

        return Inertia::render('SmsTemplates/Create', [
            'variables' => $variables
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:1600', // Limite SMS
            'segment_type' => 'nullable|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $template = SmsTemplate::create($validated);

        return redirect()->route('sms-templates.show', $template)
            ->with('success', 'Template SMS créé !');
    }

    public function show(SmsTemplate $smsTemplate): Response
    {
        return Inertia::render('SmsTemplates/Show', [
            'template' => $smsTemplate
        ]);
    }

    public function edit(SmsTemplate $smsTemplate): Response
    {
        $variables = [
            'contact' => ['business_name', 'phone', 'email', 'website', 'address', 'owner_name'],
            'campaign' => ['activity_type', 'city'],
            'rating' => ['google_rating', 'review_count'],
            'other' => ['current_date', 'current_time', 'sender_name', 'sender_company']
        ];

        return Inertia::render('SmsTemplates/Edit', [
            'template' => $smsTemplate,
            'variables' => $variables
        ]);
    }

    public function update(Request $request, SmsTemplate $smsTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:1600',
            'segment_type' => 'nullable|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $smsTemplate->update($validated);

        return back()->with('success', 'Template SMS mis à jour !');
    }

    public function destroy(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();

        return redirect()->route('sms-templates.index')
            ->with('success', 'Template SMS supprimé !');
    }

    public function sendTestSms(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1600',
            'test_phone' => 'required|string',
        ]);
        
        $sampleData = [
            'business_name' => 'Restaurant Le Gourmet',
            'owner_name' => 'Jean Dupont',
            'phone' => '01 23 45 67 89',
            'email' => 'contact@legourmet.fr',
            'website' => 'https://legourmet.fr',
            'address' => '123 Rue de la Paix, 75001 Paris',
            'activity_type' => 'restaurant',
            'city' => 'Paris',
            'google_rating' => 4.5,
            'review_count' => 125,
            'current_date' => now()->format('d/m/Y'),
            'current_time' => now()->format('H:i'),
            'sender_name' => 'Votre Nom',
            'sender_company' => 'Votre Entreprise',
        ];
        
        // Créer un template temporaire pour le rendu
        $tempTemplate = new SmsTemplate();
        $tempTemplate->content = $validated['content'];
        
        $renderedContent = $tempTemplate->renderContent($sampleData);
        
        try {
            // Initialiser Twilio
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');
            
            $twilio = new Client($sid, $token);
            
            // Envoyer le SMS de test
            $message = $twilio->messages->create(
                $validated['test_phone'],
                [
                    'from' => $from,
                    'body' => $renderedContent
                ]
            );
            
            return back()->with('success', "SMS de test envoyé au {$validated['test_phone']} !");
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi du SMS: ' . $e->getMessage());
        }
    }

    public function toggle(SmsTemplate $smsTemplate)
    {
        $smsTemplate->update([
            'is_active' => !$smsTemplate->is_active
        ]);

        $status = $smsTemplate->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Template SMS {$status} !");
    }

    public function duplicate(SmsTemplate $smsTemplate)
    {
        $duplicate = $smsTemplate->replicate();
        $duplicate->name = $smsTemplate->name . ' (Copie)';
        $duplicate->is_active = false;
        $duplicate->save();

        return redirect()->route('sms-templates.edit', $duplicate)
            ->with('success', 'Template SMS dupliqué !');
    }
}
