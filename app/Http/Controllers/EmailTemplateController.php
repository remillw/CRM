<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class EmailTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        $templates = QueryBuilder::for(EmailTemplate::class)
            ->allowedFilters(['name', 'segment_type', 'is_active'])
            ->allowedSorts(['created_at', 'name'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => EmailTemplate::count(),
            'active' => EmailTemplate::where('is_active', true)->count(),
            'inactive' => EmailTemplate::where('is_active', false)->count(),
        ];

        return Inertia::render('EmailTemplates/Index', [
            'templates' => $templates,
            'stats' => $stats,
            'filters' => $request->only(['filter', 'sort'])
        ]);
    }

    public function create(): Response
    {
        $variables = [
            'contact' => ['business_name', 'phone', 'email', 'website', 'address'],
            'campaign' => ['activity_type', 'city'],
            'rating' => ['google_rating', 'review_count'],
            'other' => ['current_date', 'current_time', 'sender_name', 'sender_company']
        ];

        return Inertia::render('EmailTemplates/Create', [
            'variables' => $variables
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'segment_type' => 'nullable|string',
            'variables' => 'nullable|array',
        ]);

        $template = EmailTemplate::create($validated);

        return redirect()->route('email-templates.show', $template)
            ->with('success', 'Template créé !');
    }

    public function show(EmailTemplate $emailTemplate): Response
    {
        return Inertia::render('EmailTemplates/Show', [
            'template' => $emailTemplate
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): Response
    {
        return Inertia::render('EmailTemplates/Edit', [
            'template' => $emailTemplate
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'segment_type' => 'nullable|string',
            'variables' => 'nullable|array',
        ]);

        $emailTemplate->update($validated);

        return back()->with('success', 'Template mis à jour !');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()->route('email-templates.index')
            ->with('success', 'Template supprimé !');
    }

    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
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
            'tracking_pixel' => '<span style="color: #999; font-size: 12px;">[PIXEL DE TRACKING]</span>',
            'unsubscribe_link' => '<span style="color: #999;">[LIEN DE DÉSINSCRIPTION]</span>',
        ];
        
        return response()->json([
            'subject' => $emailTemplate->renderSubject($sampleData),
            'content' => $emailTemplate->renderContent($sampleData)
        ]);
    }

    public function previewDraft(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string',
            'content' => 'required|string',
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
            'tracking_pixel' => '<span style="color: #999; font-size: 12px;">[PIXEL DE TRACKING]</span>',
            'unsubscribe_link' => '<span style="color: #999;">[LIEN DE DÉSINSCRIPTION]</span>',
        ];
        
        // Créer un template temporaire pour le rendu
        $tempTemplate = new EmailTemplate();
        $tempTemplate->subject = $validated['subject'];
        $tempTemplate->content = $validated['content'];
        
        return response()->json([
            'subject' => $tempTemplate->renderSubject($sampleData),
            'content' => $tempTemplate->renderContent($sampleData)
        ]);
    }

    public function duplicate(EmailTemplate $emailTemplate)
    {
        $duplicate = $emailTemplate->replicate();
        $duplicate->name = $emailTemplate->name . ' (Copie)';
        $duplicate->is_active = false;
        $duplicate->save();

        return redirect()->route('email-templates.edit', $duplicate)
            ->with('success', 'Template dupliqué !');
    }

    public function sendTestEmail(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string',
            'content' => 'required|string',
        ]);
        
        // Récupérer les emails de test depuis la configuration
        $testEmails = explode(',', env('TEST_EMAILS', ''));
        $testEmails = array_map('trim', $testEmails);
        $testEmails = array_filter($testEmails); // Supprimer les emails vides
        
        if (empty($testEmails)) {
            return back()->with('error', 'Aucun email de test configuré. Veuillez configurer TEST_EMAILS dans votre fichier .env');
        }
        
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
        $tempTemplate = new EmailTemplate();
        $tempTemplate->subject = $validated['subject'];
        $tempTemplate->content = $validated['content'];
        
        $renderedSubject = $tempTemplate->renderSubject($sampleData);
        $renderedContent = $tempTemplate->renderContent($sampleData);
        
        // Envoyer l'email de test à tous les emails configurés
        $sentCount = 0;
        foreach ($testEmails as $testEmail) {
            if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                \Mail::to($testEmail)->send(
                    new \App\Mail\CampaignEmail(
                        $renderedSubject,
                        $renderedContent,
                        null // pas de tracking pour les tests
                    )
                );
                $sentCount++;
            }
        }
        
        $emailList = implode(', ', $testEmails);
        return back()->with('success', "Email de test envoyé à {$sentCount} destinataires : {$emailList}");
    }

    public function toggle(EmailTemplate $emailTemplate)
    {
        $emailTemplate->update([
            'is_active' => !$emailTemplate->is_active
        ]);

        $status = $emailTemplate->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Template {$status} !");
    }
}
