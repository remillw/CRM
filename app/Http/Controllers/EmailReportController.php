<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;
use App\Models\EmailSend;
use App\Models\EmailTemplate;
use App\Models\Campaign;
use App\Models\EmailCampaignSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class EmailReportController extends Controller
{
    public function index(Request $request): Response
    {
        $campaigns = Campaign::with(['emailSends.contact'])
            ->withCount([
                'emailSends',
                'emailSends as opened_count' => function($query) {
                    $query->whereNotNull('opened_at');
                },
                'emailSends as clicked_count' => function($query) {
                    $query->whereNotNull('clicked_at');
                }
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Statistiques globales (incluant les campagnes programmées)
        $totalSent = EmailSend::count();
        $totalOpened = EmailSend::whereNotNull('opened_at')->count();
        $totalClicked = EmailSend::whereNotNull('clicked_at')->count();
        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;

        // Emails nécessitant une relance (pas ouverts après 3 jours)
        $needFollowUp = EmailSend::where('sent_at', '<=', now()->subDays(3))
            ->whereNull('opened_at')
            ->with(['contact', 'campaign'])
            ->limit(10)
            ->get();

        // Templates les plus performants
        $topTemplates = EmailTemplate::withCount([
                'emailCampaigns as sent_count' => function($query) {
                    $query->join('email_sends', 'email_campaigns.id', '=', 'email_sends.campaign_id');
                },
                'emailCampaigns as opened_count' => function($query) {
                    $query->join('email_sends', 'email_campaigns.id', '=', 'email_sends.campaign_id')
                          ->whereNotNull('email_sends.opened_at');
                }
            ])
            ->having('sent_count', '>', 0)
            ->orderByRaw('(opened_count / sent_count) DESC')
            ->limit(5)
            ->get()
            ->map(function($template) {
                $template->open_rate = $template->sent_count > 0 
                    ? round(($template->opened_count / $template->sent_count) * 100, 1) 
                    : 0;
                return $template;
            });

        // Statistiques des emails de test
        $testCampaigns = EmailCampaignSchedule::where('is_test', true)
            ->with(['emailSends.contact'])
            ->withCount([
                'emailSends',
                'emailSends as test_opened_count' => function($query) {
                    $query->whereNotNull('opened_at');
                },
                'emailSends as test_clicked_count' => function($query) {
                    $query->whereNotNull('clicked_at');
                }
            ])
            ->latest()
            ->limit(10)
            ->get();

        $testTotalSent = $testCampaigns->sum('email_sends_count');
        $testTotalOpened = $testCampaigns->sum('test_opened_count');
        $testTotalClicked = $testCampaigns->sum('test_clicked_count');
        $testOpenRate = $testTotalSent > 0 ? round(($testTotalOpened / $testTotalSent) * 100, 1) : 0;
        $testClickRate = $testTotalSent > 0 ? round(($testTotalClicked / $testTotalSent) * 100, 1) : 0;

        return Inertia::render('EmailReports/Index', [
            'campaigns' => $campaigns,
            'stats' => [
                'total_sent' => $totalSent,
                'total_opened' => $totalOpened,
                'total_clicked' => $totalClicked,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
            ],
            'testStats' => [
                'total_sent' => $testTotalSent,
                'total_opened' => $testTotalOpened,
                'total_clicked' => $testTotalClicked,
                'open_rate' => $testOpenRate,
                'click_rate' => $testClickRate,
            ],
            'testCampaigns' => $testCampaigns,
            'needFollowUp' => $needFollowUp,
            'topTemplates' => $topTemplates,
        ]);
    }

    public function campaign(Campaign $campaign): Response
    {
        $campaign->load([
            'emailSends.contact',
            'emailSends' => function($query) {
                $query->latest('sent_at');
            }
        ]);

        // Statistiques de la campagne
        $totalSent = $campaign->emailSends->count();
        $totalOpened = $campaign->emailSends->whereNotNull('opened_at')->count();
        $totalClicked = $campaign->emailSends->whereNotNull('clicked_at')->count();
        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;

        // Évolution dans le temps (7 derniers jours)
        $evolution = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayEmails = $campaign->emailSends()->whereDate('sent_at', $date)->get();
            
            $evolution[] = [
                'date' => $date,
                'sent' => $dayEmails->count(),
                'opened' => $dayEmails->whereNotNull('opened_at')->count(),
                'clicked' => $dayEmails->whereNotNull('clicked_at')->count(),
            ];
        }

        // Emails par statut
        $emailsByStatus = [
            'sent' => $campaign->emailSends()->whereNull('opened_at')->whereNull('clicked_at')->count(),
            'opened' => $campaign->emailSends()->whereNotNull('opened_at')->whereNull('clicked_at')->count(),
            'clicked' => $campaign->emailSends()->whereNotNull('clicked_at')->count(),
            'bounced' => $campaign->emailSends()->whereNotNull('bounced_at')->count(),
        ];

        // Relances programmées
        $followUps = $this->getScheduledFollowUps($campaign);

        return Inertia::render('EmailReports/Campaign', [
            'campaign' => $campaign,
            'stats' => [
                'total_sent' => $totalSent,
                'total_opened' => $totalOpened,
                'total_clicked' => $totalClicked,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
            ],
            'evolution' => $evolution,
            'emailsByStatus' => $emailsByStatus,
            'followUps' => $followUps,
        ]);
    }

    private function getScheduledFollowUps(Campaign $campaign): array
    {
        $followUps = [];
        
        // Première relance : emails pas ouverts après 3 jours
        $firstFollowUp = $campaign->emailSends()
            ->where('sent_at', '<=', now()->subDays(3))
            ->whereNull('opened_at')
            ->count();
            
        if ($firstFollowUp > 0) {
            $followUps[] = [
                'type' => 'Première relance',
                'count' => $firstFollowUp,
                'template' => 'Relance - Pas de réponse première prospection',
                'scheduled_for' => 'Maintenant',
                'description' => 'Emails non ouverts après 3 jours'
            ];
        }

        // Seconde relance : emails pas ouverts après 7 jours
        $secondFollowUp = $campaign->emailSends()
            ->where('sent_at', '<=', now()->subDays(7))
            ->whereNull('opened_at')
            ->count();
            
        if ($secondFollowUp > 0) {
            $followUps[] = [
                'type' => 'Seconde relance',
                'count' => $secondFollowUp,
                'template' => 'Relance - Seconde relance',
                'scheduled_for' => 'Maintenant',
                'description' => 'Emails non ouverts après 7 jours'
            ];
        }

        // Relance pour emails ouverts sans réponse
        $openedNoResponse = $campaign->emailSends()
            ->whereNotNull('opened_at')
            ->whereNull('clicked_at')
            ->where('opened_at', '<=', now()->subDays(2))
            ->count();
            
        if ($openedNoResponse > 0) {
            $followUps[] = [
                'type' => 'Email ouvert sans réponse',
                'count' => $openedNoResponse,
                'template' => 'Email ouvert mais pas de réponse',
                'scheduled_for' => 'Dans 1 jour',
                'description' => 'Emails ouverts mais sans clic après 2 jours'
            ];
        }

        return $followUps;
    }

    public function sendFollowUp(Request $request, Campaign $campaign)
    {
        $request->validate([
            'template_name' => 'required|string',
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contacts,id',
        ]);

        $template = EmailTemplate::where('name', $request->template_name)->first();
        
        if (!$template) {
            return back()->with('error', 'Template non trouvé.');
        }

        // Créer les EmailSend pour la relance
        $sentCount = 0;
        foreach ($request->contact_ids as $contactId) {
            // Logique d'envoi d'email (à implémenter selon votre système d'envoi)
            $sentCount++;
        }

        return back()->with('success', "Relance programmée pour {$sentCount} contacts avec le template '{$template->name}'.");
    }

    public function campaignSchedule(EmailCampaignSchedule $emailCampaignSchedule): Response
    {
        $emailCampaignSchedule->load([
            'emailSends.contact',
            'template',
            'emailSends' => function($query) {
                $query->latest('sent_at');
            }
        ]);

        // Statistiques de la campagne programmée
        $totalSent = $emailCampaignSchedule->emailSends->count();
        $totalOpened = $emailCampaignSchedule->emailSends->whereNotNull('opened_at')->count();
        $totalClicked = $emailCampaignSchedule->emailSends->whereNotNull('clicked_at')->count();
        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;

        // Séparer les contacts qui ont ouvert vs ceux qui n'ont pas ouvert
        $emailsOpened = $emailCampaignSchedule->emailSends->whereNotNull('opened_at');
        $emailsNotOpened = $emailCampaignSchedule->emailSends->whereNull('opened_at');

        // Templates disponibles pour relance
        $templates = EmailTemplate::active()->select('id', 'name', 'subject')->get();

        return Inertia::render('EmailReports/CampaignSchedule', [
            'campaign' => $emailCampaignSchedule,
            'stats' => [
                'total_sent' => $totalSent,
                'total_opened' => $totalOpened,
                'total_clicked' => $totalClicked,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
            ],
            'emailsOpened' => $emailsOpened->values(),
            'emailsNotOpened' => $emailsNotOpened->values(),
            'templates' => $templates,
        ]);
    }

    public function sendScheduleFollowUp(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'email_send_ids' => 'required|array',
            'email_send_ids.*' => 'exists:email_sends,id',
        ]);

        $template = EmailTemplate::find($validated['template_id']);
        $emailSends = EmailSend::whereIn('id', $validated['email_send_ids'])->with('contact')->get();

        // Créer une nouvelle campagne de test pour la relance
        $schedule = EmailCampaignSchedule::create([
            'name' => 'Relance - ' . $template->name . ' - ' . now()->format('d/m/Y H:i'),
            'template_id' => $template->id,
            'contact_list_ids' => [], // Pas de liste spécifique, contacts sélectionnés
            'scheduled_at' => now(),
            'is_test' => true,
            'total_recipients' => $emailSends->count(),
        ]);

        // Créer les nouveaux EmailSend pour la relance
        foreach ($emailSends as $originalSend) {
            EmailSend::create([
                'campaign_schedule_id' => $schedule->id,
                'contact_id' => $originalSend->contact_id,
                'tracking_id' => Str::uuid(),
                'status' => 'sent',
                'sent_at' => now(),
                'template_name' => $template->name . ' (Relance)',
            ]);
        }

        return back()->with('success', "Relance envoyée à " . $emailSends->count() . " contacts avec le template \"{$template->name}\".");
    }
}