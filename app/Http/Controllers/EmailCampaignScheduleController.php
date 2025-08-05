<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignSchedule;
use App\Models\EmailTemplate;
use App\Models\ContactList;
use App\Models\Campaign;
use App\Jobs\SendScheduledEmails;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailCampaignScheduleController extends Controller
{
    public function index(): Response
    {
        $schedules = EmailCampaignSchedule::with(['template'])
            ->withCount('emailSends')
            ->latest('scheduled_at')
            ->paginate(15)
            ->withQueryString();

        // Statistiques
        $stats = [
            'total_schedules' => EmailCampaignSchedule::count(),
            'scheduled_count' => EmailCampaignSchedule::where('status', 'scheduled')->count(),
            'sending_count' => EmailCampaignSchedule::where('status', 'sending')->count(),
            'sent_count' => EmailCampaignSchedule::where('status', 'sent')->count(),
            'due_count' => EmailCampaignSchedule::due()->count(),
        ];

        return Inertia::render('EmailCampaignSchedules/Index', [
            'schedules' => $schedules,
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        $templates = EmailTemplate::active()->select('id', 'name', 'subject', 'segment_type')->get();
        $contactLists = ContactList::active()->withCount('contacts as contacts_count')->get();
        $campaigns = Campaign::select('id', 'name', 'activity_type', 'city')->get();

        return Inertia::render('EmailCampaignSchedules/Create', [
            'templates' => $templates,
            'contactLists' => $contactLists,
            'campaigns' => $campaigns,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:email_templates,id',
            'contact_list_ids' => 'required|array|min:1',
            'contact_list_ids.*' => 'exists:contact_lists,id',
            'campaign_ids' => 'nullable|array',
            'campaign_ids.*' => 'exists:campaigns,id',
            'send_type' => 'required|in:now,scheduled',
            'scheduled_date' => 'nullable|date|after:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'send_options' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_test' => 'nullable|boolean',
        ]);

        // Préparer la date/heure programmée
        if ($validated['send_type'] === 'scheduled') {
            $scheduledAt = $validated['scheduled_date'] . ' ' . ($validated['scheduled_time'] ?? '09:00');
            $validated['scheduled_at'] = $scheduledAt;
        } else {
            $validated['scheduled_at'] = now();
        }

        $schedule = EmailCampaignSchedule::create($validated);
        
        // Calculer le nombre de destinataires
        $totalRecipients = $schedule->calculateTotalRecipients();
        $schedule->update(['total_recipients' => $totalRecipients]);

        // Si c'est un envoi immédiat ou un test, lancer l'envoi
        if ($validated['send_type'] === 'now' || $request->boolean('is_test')) {
            if ($request->boolean('is_test')) {
                // Pour les tests, exécuter de manière synchrone
                try {
                    SendScheduledEmails::dispatchSync($schedule);
                    return redirect()->route('email-campaign-schedules.create')
                        ->with('success', "Test email envoyé avec succès ! {$totalRecipients} destinataires de test ciblés.");
                } catch (\Exception $e) {
                    return redirect()->route('email-campaign-schedules.create')
                        ->with('error', "Erreur lors de l'envoi du test : " . $e->getMessage());
                }
            } else {
                // Pour les envois normaux, utiliser la queue
                SendScheduledEmails::dispatch($schedule);
                return redirect()->route('email-campaign-schedules.index')
                    ->with('success', "Campagne email envoyée avec succès ! {$totalRecipients} destinataires ciblés.");
            }
        }

        return redirect()->route('email-campaign-schedules.index')
            ->with('success', "Campagne email programmée avec succès ! {$totalRecipients} destinataires ciblés.");
    }


    public function edit(EmailCampaignSchedule $emailCampaignSchedule): Response
    {
        // Seules les campagnes programmées peuvent être modifiées
        if ($emailCampaignSchedule->status !== 'scheduled') {
            return redirect()->route('email-campaign-schedules.index')
                ->with('error', 'Seules les campagnes programmées peuvent être modifiées.');
        }

        $templates = EmailTemplate::active()->select('id', 'name', 'subject', 'segment_type')->get();
        $contactLists = ContactList::active()->withCount('contacts as contacts_count')->get();
        $campaigns = Campaign::select('id', 'name', 'activity_type', 'city')->get();

        return Inertia::render('EmailCampaignSchedules/Edit', [
            'schedule' => $emailCampaignSchedule,
            'templates' => $templates,
            'contactLists' => $contactLists,
            'campaigns' => $campaigns,
        ]);
    }

    public function update(Request $request, EmailCampaignSchedule $emailCampaignSchedule)
    {
        if ($emailCampaignSchedule->status !== 'scheduled') {
            return back()->with('error', 'Seules les campagnes programmées peuvent être modifiées.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_id' => 'required|exists:email_templates,id',
            'contact_list_ids' => 'required|array|min:1',
            'contact_list_ids.*' => 'exists:contact_lists,id',
            'campaign_ids' => 'nullable|array',
            'campaign_ids.*' => 'exists:campaigns,id',
            'scheduled_at' => 'required|date|after:now',
            'send_options' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $emailCampaignSchedule->update($validated);
        
        // Recalculer le nombre de destinataires
        $totalRecipients = $emailCampaignSchedule->calculateTotalRecipients();
        $emailCampaignSchedule->update(['total_recipients' => $totalRecipients]);

        return redirect()->route('email-campaign-schedules.index')
            ->with('success', "Campagne email mise à jour ! {$totalRecipients} destinataires ciblés.");
    }

    public function destroy(EmailCampaignSchedule $emailCampaignSchedule)
    {
        if ($emailCampaignSchedule->status === 'sending') {
            return back()->with('error', 'Impossible de supprimer une campagne en cours d\'envoi.');
        }

        $emailCampaignSchedule->delete();

        return redirect()->route('email-campaign-schedules.index')
            ->with('success', 'Campagne email supprimée avec succès.');
    }

    public function sendNow(EmailCampaignSchedule $emailCampaignSchedule)
    {
        if (!$emailCampaignSchedule->canBeSent()) {
            return back()->with('error', 'Cette campagne ne peut pas être envoyée maintenant.');
        }

        // Lancer l'envoi immédiatement
        SendScheduledEmails::dispatch($emailCampaignSchedule);

        return back()->with('success', 'Campagne email lancée immédiatement !');
    }

    public function duplicate(EmailCampaignSchedule $emailCampaignSchedule)
    {
        $newSchedule = $emailCampaignSchedule->replicate();
        $newSchedule->name = $emailCampaignSchedule->name . ' (Copie)';
        $newSchedule->scheduled_at = now()->addHour();
        $newSchedule->status = 'scheduled';
        $newSchedule->sent_count = 0;
        $newSchedule->failed_count = 0;
        $newSchedule->started_at = null;
        $newSchedule->completed_at = null;
        $newSchedule->save();

        return redirect()->route('email-campaign-schedules.edit', $newSchedule)
            ->with('success', 'Campagne dupliquée avec succès !');
    }

    public function preview(EmailCampaignSchedule $emailCampaignSchedule)
    {
        $template = $emailCampaignSchedule->template;
        $sampleContact = $emailCampaignSchedule->getTargetContacts()->first();
        
        if (!$sampleContact) {
            return back()->with('error', 'Aucun contact ciblé trouvé pour l\'aperçu.');
        }

        // Préparer les variables pour l'aperçu
        $variables = [
            'business_name' => $sampleContact->business_name,
            'owner_name' => $sampleContact->owner_name ?: $sampleContact->business_name,
            'activity_type' => $sampleContact->campaign->activity_type ?? 'entreprise',
            'city' => $sampleContact->campaign->city ?? 'votre ville',
            'website' => $sampleContact->website,
            'tracking_pixel' => '<span style="color: #999; font-size: 12px;">[PIXEL DE TRACKING]</span>',
            'unsubscribe_link' => '<span style="color: #999;">[LIEN DE DÉSINSCRIPTION]</span>',
        ];

        $previewSubject = $template->renderSubject($variables);
        $previewContent = $template->renderContent($variables);

        return Inertia::render('EmailCampaignSchedules/Preview', [
            'schedule' => $emailCampaignSchedule,
            'template' => $template,
            'sampleContact' => $sampleContact,
            'previewSubject' => $previewSubject,
            'previewContent' => $previewContent,
        ]);
    }

    public function sendAllDue()
    {
        $dueSchedules = EmailCampaignSchedule::due()->get();
        
        if ($dueSchedules->isEmpty()) {
            return back()->with('error', 'Aucune campagne due pour envoi.');
        }

        $jobsLaunched = 0;
        foreach ($dueSchedules as $schedule) {
            if ($schedule->canBeSent()) {
                SendScheduledEmails::dispatch($schedule);
                $jobsLaunched++;
            }
        }

        return back()->with('success', "{$jobsLaunched} campagne(s) lancée(s) pour envoi !");
    }
}