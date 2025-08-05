<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\EmailSend;
use App\Models\EmailCampaign;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->get('period', '30'); // 7, 30, 90 jours
        $startDate = Carbon::now()->subDays($period);

        // Métriques principales
        $metrics = [
            'total_contacts' => Contact::count(),
            'total_campaigns' => Campaign::count(),
            'completed_campaigns' => Campaign::where('status', 'completed')->count(),
            'contacts_with_email' => Contact::withEmail()->count(),
            'contacts_with_website' => Contact::withWebsite()->count(),
            'average_rating' => round(Contact::whereNotNull('google_rating')->avg('google_rating'), 1),
            'total_emails_sent' => EmailSend::count(),
            'recent_contacts' => Contact::where('created_at', '>=', $startDate)->count(),
        ];

        // Évolution des contacts par jour (30 derniers jours)
        $contactsEvolution = Contact::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count
                ];
            });

        // Répartition par type d'activité
        $activitiesStats = Campaign::selectRaw('activity_type, COUNT(*) as campaign_count, SUM((SELECT COUNT(*) FROM contacts WHERE campaign_id = campaigns.id)) as contact_count')
            ->groupBy('activity_type')
            ->having('contact_count', '>', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'activity' => $item->activity_type,
                    'campaigns' => $item->campaign_count,
                    'contacts' => $item->contact_count ?? 0
                ];
            });

        // Top villes par nombre de contacts
        $citiesStats = Campaign::selectRaw('city, COUNT(*) as campaign_count, SUM((SELECT COUNT(*) FROM contacts WHERE campaign_id = campaigns.id)) as contact_count')
            ->groupBy('city')
            ->having('contact_count', '>', 0)
            ->orderBy('contact_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'city' => $item->city,
                    'campaigns' => $item->campaign_count,
                    'contacts' => $item->contact_count ?? 0
                ];
            });

        // Performances des campagnes récentes
        $recentCampaigns = Campaign::with('contacts')
            ->where('created_at', '>=', $startDate)
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'activity_type' => $campaign->activity_type,
                    'city' => $campaign->city,
                    'target_count' => $campaign->target_count,
                    'actual_count' => $campaign->contacts->count(),
                    'success_rate' => $campaign->target_count > 0 ? 
                        round(($campaign->contacts->count() / $campaign->target_count) * 100, 1) : 0,
                    'with_email' => $campaign->contacts->whereNotNull('email')->count(),
                    'with_website' => $campaign->contacts->whereNotNull('website')->count(),
                    'avg_rating' => round($campaign->contacts->avg('google_rating'), 1),
                    'created_at' => $campaign->created_at->format('d/m/Y'),
                    'status' => $campaign->status
                ];
            });

        // Statistiques de qualité des contacts
        $qualityStats = [
            'excellent' => Contact::where('google_rating', '>=', 4.5)->count(),
            'good' => Contact::whereBetween('google_rating', [4.0, 4.4])->count(),
            'average' => Contact::whereBetween('google_rating', [3.0, 3.9])->count(),
            'poor' => Contact::where('google_rating', '<', 3.0)->count(),
            'no_rating' => Contact::whereNull('google_rating')->count(),
        ];

        // Taux de succès par type d'activité
        $successRates = Campaign::selectRaw('
                activity_type,
                AVG(CASE 
                    WHEN target_count > 0 THEN ((SELECT COUNT(*) FROM contacts WHERE campaign_id = campaigns.id) / target_count) * 100 
                    ELSE 0 
                END) as avg_success_rate,
                COUNT(*) as campaign_count
            ')
            ->where('status', 'completed')
            ->groupBy('activity_type')
            ->havingRaw('COUNT(*) >= 2') // Au moins 2 campagnes pour calculer une moyenne
            ->get()
            ->map(function ($item) {
                return [
                    'activity' => $item->activity_type,
                    'success_rate' => round($item->avg_success_rate, 1),
                    'campaigns' => $item->campaign_count
                ];
            });

        return Inertia::render('Analytics/Index', [
            'metrics' => $metrics,
            'contactsEvolution' => $contactsEvolution,
            'activitiesStats' => $activitiesStats,
            'citiesStats' => $citiesStats,
            'recentCampaigns' => $recentCampaigns,
            'qualityStats' => $qualityStats,
            'successRates' => $successRates,
            'period' => $period
        ]);
    }

    public function campaigns(Request $request): Response
    {
        $campaigns = Campaign::with('contacts')
            ->latest()
            ->paginate(20)
            ->through(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'activity_type' => $campaign->activity_type,
                    'city' => $campaign->city,
                    'target_count' => $campaign->target_count,
                    'actual_count' => $campaign->contacts->count(),
                    'success_rate' => $campaign->target_count > 0 ? 
                        round(($campaign->contacts->count() / $campaign->target_count) * 100, 1) : 0,
                    'contacts_with_email' => $campaign->contacts->whereNotNull('email')->count(),
                    'contacts_with_website' => $campaign->contacts->whereNotNull('website')->count(),
                    'avg_rating' => round($campaign->contacts->avg('google_rating'), 1),
                    'duration' => $campaign->started_at && $campaign->completed_at ? 
                        $campaign->started_at->diffInMinutes($campaign->completed_at) : null,
                    'created_at' => $campaign->created_at->format('d/m/Y H:i'),
                    'status' => $campaign->status
                ];
            });

        return Inertia::render('Analytics/Campaigns', [
            'campaigns' => $campaigns
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'overview');
        $period = $request->get('period', 30);
        $startDate = Carbon::now()->subDays($period);

        switch ($type) {
            case 'campaigns':
                return $this->exportCampaigns($startDate);
            case 'contacts':
                return $this->exportContacts($startDate);
            case 'overview':
            default:
                return $this->exportOverview($startDate);
        }
    }

    private function exportOverview($startDate)
    {
        $metrics = [
            'total_contacts' => Contact::count(),
            'total_campaigns' => Campaign::count(),
            'contacts_with_email' => Contact::withEmail()->count(),
            'contacts_with_website' => Contact::withWebsite()->count(),
            'recent_contacts' => Contact::where('created_at', '>=', $startDate)->count(),
        ];

        $csv = "Métrique,Valeur\n";
        foreach ($metrics as $key => $value) {
            $csv .= "\"{$key}\",{$value}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="analytics-overview-' . date('Y-m-d') . '.csv"');
    }

    private function exportCampaigns($startDate)
    {
        $campaigns = Campaign::with('contacts')
            ->where('created_at', '>=', $startDate)
            ->get();

        $csv = "Nom,Type Activité,Ville,Objectif,Résultat,Taux Succès,Avec Email,Avec Site,Note Moyenne,Status,Date\n";
        
        foreach ($campaigns as $campaign) {
            $actualCount = $campaign->contacts->count();
            $successRate = $campaign->target_count > 0 ? 
                round(($actualCount / $campaign->target_count) * 100, 1) : 0;
            
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $campaign->name) . '"',
                '"' . $campaign->activity_type . '"',
                '"' . $campaign->city . '"',
                $campaign->target_count,
                $actualCount,
                $successRate . '%',
                $campaign->contacts->whereNotNull('email')->count(),
                $campaign->contacts->whereNotNull('website')->count(),
                round($campaign->contacts->avg('google_rating'), 1),
                $campaign->status,
                $campaign->created_at->format('d/m/Y')
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="analytics-campaigns-' . date('Y-m-d') . '.csv"');
    }

    private function exportContacts($startDate)
    {
        $contacts = Contact::with('campaign')
            ->where('created_at', '>=', $startDate)
            ->get();

        $csv = "Entreprise,Email,Téléphone,Site Web,Ville,Note Google,Nb Avis,Campagne,Date Ajout\n";
        
        foreach ($contacts as $contact) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $contact->business_name) . '"',
                '"' . ($contact->email ?? '') . '"',
                '"' . ($contact->phone ?? '') . '"',
                '"' . ($contact->website ?? '') . '"',
                '"' . ($contact->campaign->city ?? '') . '"',
                $contact->google_rating ?? '',
                $contact->review_count ?? '',
                '"' . ($contact->campaign->name ?? '') . '"',
                $contact->created_at->format('d/m/Y')
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="analytics-contacts-' . date('Y-m-d') . '.csv"');
    }
}