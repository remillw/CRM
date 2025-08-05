<?php

namespace App\Http\Controllers;

use App\Models\SeoQuery;
use App\Models\SeoResult;
use App\Models\Campaign;
use App\Jobs\AnalyzeCustomSeoQuery;
use App\Jobs\ScrapeGoogleResults;
use App\Jobs\AnalyzeCampaignSeoQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class SeoQueryController extends Controller
{
    public function index(Request $request): Response
    {
        $queries = QueryBuilder::for(SeoQuery::class)
            ->allowedFilters(['name', 'query', 'is_active'])
            ->allowedSorts(['created_at', 'name', 'last_analyzed_at'])
            ->withCount('results')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_queries' => SeoQuery::count(),
            'active_queries' => SeoQuery::where('is_active', true)->count(),
            'due_for_analysis' => SeoQuery::dueForAnalysis()->count(),
            'total_results' => SeoResult::count(),
        ];

        return Inertia::render('SeoQueries/Index', [
            'queries' => $queries,
            'stats' => $stats,
            'filters' => $request->only(['filter', 'sort'])
        ]);
    }

    public function create(): Response
    {
        $campaigns = Campaign::select('id', 'name', 'activity_type', 'city')->get();
        
        return Inertia::render('SeoQueries/Create', [
            'campaigns' => $campaigns
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'query' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'max_pages' => 'nullable|integer|min:1|max:50',
            'frequency' => 'required|in:daily,weekly,monthly,one-time',
            'target_campaigns' => 'nullable|array',
            'target_campaigns.*' => 'exists:campaigns,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Gérer le type one-time
        if ($validated['frequency'] === 'one-time') {
            $validated['is_one_time'] = true;
            $validated['frequency'] = 'weekly'; // Valeur par défaut pour la base
        } else {
            $validated['is_one_time'] = false;
        }

        $seoQuery = SeoQuery::create($validated);
        $seoQuery->scheduleNextAnalysis();

        return redirect()->route('seo-queries.show', $seoQuery)
            ->with('success', 'Requête SEO créée avec succès !');
    }

    public function show(SeoQuery $seoQuery): Response
    {
        $seoQuery->load(['results.contact']);
        
        // Résultats récents groupés par contact
        $recentResults = $seoQuery->results()
            ->with('contact')
            ->latest('analyzed_at')
            ->take(50)
            ->get()
            ->groupBy('contact_id');

        // Statistiques de la requête
        $stats = [
            'total_contacts_tracked' => $seoQuery->results()->distinct('contact_id')->count(),
            'currently_ranking' => $seoQuery->results()->where('found', true)->latest('analyzed_at')->distinct('contact_id')->count(),
            'average_position' => $seoQuery->results()->where('found', true)->avg('position'),
            'last_analysis' => $seoQuery->last_analyzed_at,
            'next_analysis' => $seoQuery->next_analysis_at,
        ];

        // Évolution historique (derniers 30 jours)
        $evolution = $seoQuery->results()
            ->selectRaw('DATE(analyzed_at) as date, AVG(position) as avg_position, COUNT(*) as total_results')
            ->where('analyzed_at', '>=', now()->subDays(30))
            ->where('found', true)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('SeoQueries/Show', [
            'seoQuery' => $seoQuery,
            'recentResults' => $recentResults,
            'stats' => $stats,
            'evolution' => $evolution
        ]);
    }

    public function edit(SeoQuery $seoQuery): Response
    {
        $campaigns = Campaign::select('id', 'name', 'activity_type', 'city')->get();
        
        return Inertia::render('SeoQueries/Edit', [
            'seoQuery' => $seoQuery,
            'campaigns' => $campaigns
        ]);
    }

    public function update(Request $request, SeoQuery $seoQuery)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'query' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'max_pages' => 'nullable|integer|min:1|max:50',
            'frequency' => 'required|in:daily,weekly,monthly,one-time',
            'target_campaigns' => 'nullable|array',
            'target_campaigns.*' => 'exists:campaigns,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Gérer le type one-time
        if ($validated['frequency'] === 'one-time') {
            $validated['is_one_time'] = true;
            $validated['frequency'] = 'weekly'; // Valeur par défaut pour la base
        } else {
            $validated['is_one_time'] = false;
        }

        $seoQuery->update($validated);
        
        // Reprogrammer si la fréquence a changé
        if ($request->has('frequency')) {
            $seoQuery->scheduleNextAnalysis();
        }

        return back()->with('success', 'Requête SEO mise à jour !');
    }

    public function destroy(SeoQuery $seoQuery)
    {
        $resultsCount = $seoQuery->results()->count();
        $seoQuery->delete(); // La suppression en cascade se fait automatiquement

        return redirect()->route('seo-queries.index')
            ->with('success', "Requête SEO supprimée avec {$resultsCount} résultats associés !");
    }

    public function relaunch(SeoQuery $seoQuery)
    {
        if (!$seoQuery->canRelaunch()) {
            return back()->with('error', 'Cette requête SEO ne peut pas être relancée.');
        }

        $targetContacts = $seoQuery->getTargetContacts()->whereNotNull('website')->get();
        
        if ($targetContacts->isEmpty()) {
            return back()->with('error', 'Aucun contact avec site web trouvé pour cette requête.');
        }

        // Réinitialiser la requête si nécessaire
        $seoQuery->relaunch();

        // Lancer l'analyse de campagne
        AnalyzeCampaignSeoQuery::dispatch($seoQuery, $seoQuery->max_pages ?? 20);

        $message = "Requête SEO relancée pour {$targetContacts->count()} contacts avec la requête '{$seoQuery->query}'.";
        if ($seoQuery->is_one_time) {
            $message .= " La requête ponctuelle a été réinitialisée.";
        }

        return back()->with('success', $message);
    }

    public function analyze(SeoQuery $seoQuery)
    {
        if (!$seoQuery->canExecute()) {
            if ($seoQuery->is_one_time && $seoQuery->executed_at) {
                return back()->with('error', 'Cette requête ponctuelle a déjà été exécutée le ' . $seoQuery->executed_at->format('d/m/Y à H:i') . '.');
            }
            return back()->with('error', 'Cette requête SEO ne peut pas être exécutée.');
        }

        $targetContacts = $seoQuery->getTargetContacts()->get();
        
        if ($targetContacts->isEmpty()) {
            return back()->with('error', 'Aucun contact avec site web trouvé pour cette requête.');
        }

        // Utiliser la nouvelle méthode d'analyse de campagne (beaucoup plus efficace!)
        AnalyzeCampaignSeoQuery::dispatch($seoQuery, $seoQuery->max_pages ?? 20);

        $seoQuery->markAsExecuted();

        $message = "Analyse SEO lancée pour {$targetContacts->count()} contacts avec la requête '{$seoQuery->query}'.";
        if ($seoQuery->is_one_time) {
            $message .= " Cette requête ponctuelle ne sera plus exécutée automatiquement.";
        }

        return back()->with('success', $message);
    }

    public function toggle(SeoQuery $seoQuery)
    {
        $seoQuery->update(['is_active' => !$seoQuery->is_active]);
        
        $status = $seoQuery->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "Requête SEO {$status} !");
    }

    public function compare(Request $request): Response
    {
        $request->validate([
            'query_ids' => 'required|array|min:2|max:5',
            'query_ids.*' => 'exists:seo_queries,id',
            'date_range' => 'required|in:7,30,90',
        ]);

        $queries = SeoQuery::whereIn('id', $request->query_ids)->get();
        $dateRange = (int) $request->date_range;
        
        $comparisonData = [];
        
        foreach ($queries as $query) {
            $results = $query->results()
                ->with('contact')
                ->where('analyzed_at', '>=', now()->subDays($dateRange))
                ->where('found', true)
                ->get();
                
            $comparisonData[] = [
                'query' => $query,
                'results' => $results,
                'average_position' => $results->avg('position'),
                'best_position' => $results->min('position'),
                'total_rankings' => $results->count(),
                'evolution' => $results->groupBy(function($item) {
                    return $item->analyzed_at->format('Y-m-d');
                })->map(function($dayResults) {
                    return $dayResults->avg('position');
                })
            ];
        }

        return Inertia::render('SeoQueries/Compare', [
            'comparisonData' => $comparisonData,
            'dateRange' => $dateRange
        ]);
    }

    public function results(SeoQuery $seoQuery, Request $request): Response
    {
        $results = QueryBuilder::for($seoQuery->results())
            ->allowedFilters(['found'])
            ->allowedSorts(['analyzed_at', 'position'])
            ->with(['contact'])
            ->latest('analyzed_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('SeoQueries/Results', [
            'seoQuery' => $seoQuery,
            'results' => $results,
            'filters' => $request->only(['filter', 'sort'])
        ]);
    }

    public function analyzeAll()
    {
        $dueQueries = SeoQuery::dueForAnalysis()->get();
        
        if ($dueQueries->isEmpty()) {
            return back()->with('error', 'Aucune requête SEO n\'est due pour analyse.');
        }

        $totalJobs = 0;
        foreach ($dueQueries as $query) {
            $targetContacts = $query->getTargetContacts()->get();
            
            if (!$targetContacts->isEmpty()) {
                // Utiliser l'analyse de campagne efficace
                AnalyzeCampaignSeoQuery::dispatch($query, $query->max_pages ?? 20);
                $totalJobs++;
            }
            
            $query->update(['last_analyzed_at' => now()]);
            $query->scheduleNextAnalysis();
        }

        return back()->with('success', "Analyse lancée pour {$dueQueries->count()} requêtes ({$totalJobs} analyses programmées).");
    }

    public function analyzeMultiple(Request $request)
    {
        $request->validate([
            'query_ids' => 'required|array',
            'query_ids.*' => 'exists:seo_queries,id',
        ]);

        $queries = SeoQuery::whereIn('id', $request->query_ids)->where('is_active', true)->get();
        
        if ($queries->isEmpty()) {
            return back()->with('error', 'Aucune requête active trouvée.');
        }

        $totalJobs = 0;
        foreach ($queries as $query) {
            $targetContacts = $query->getTargetContacts()->get();
            
            if (!$targetContacts->isEmpty()) {
                // Utiliser l'analyse de campagne efficace
                AnalyzeCampaignSeoQuery::dispatch($query, $query->max_pages ?? 20);
                $totalJobs++;
            }
            
            $query->update(['last_analyzed_at' => now()]);
            $query->scheduleNextAnalysis();
        }

        return back()->with('success', "Analyse lancée pour {$queries->count()} requêtes ({$totalJobs} analyses programmées).");
    }

    public function analyzeContact(SeoQuery $seoQuery, $contactId)
    {
        $contact = \App\Models\Contact::findOrFail($contactId);
        
        if (!$contact->website) {
            return back()->with('error', 'Ce contact n\'a pas de site web.');
        }

        ScrapeGoogleResults::dispatch($contact, $seoQuery);
        
        return back()->with('success', "Analyse SEO lancée pour {$contact->business_name}.");
    }

    public function export(SeoQuery $seoQuery)
    {
        $results = $seoQuery->results()->with('contact')->get();
        
        $csvData = [];
        $csvData[] = ['Contact', 'Site Web', 'Requête', 'Position', 'Trouvé', 'URL Trouvée', 'Analysé le'];
        
        foreach ($results as $result) {
            $csvData[] = [
                $result->contact->business_name,
                $result->contact->website,
                $result->query_used,
                $result->position ?: 'Non trouvé',
                $result->found ? 'Oui' : 'Non',
                $result->url_found ?: '',
                $result->analyzed_at->format('d/m/Y H:i'),
            ];
        }
        
        $filename = "seo-query-{$seoQuery->id}-" . now()->format('Y-m-d') . '.csv';
        
        $handle = fopen('php://output', 'w');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        
        return response()->stream(function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportResults(SeoQuery $seoQuery, Request $request)
    {
        return $this->export($seoQuery);
    }

    public function exportComparison(Request $request)
    {
        $request->validate([
            'query_ids' => 'required|array',
            'query_ids.*' => 'exists:seo_queries,id',
            'date_range' => 'required|integer|min:1',
        ]);

        $queries = SeoQuery::whereIn('id', $request->query_ids)->get();
        $dateRange = (int) $request->date_range;
        
        $csvData = [];
        $csvData[] = ['Requête', 'Contact', 'Position Moyenne', 'Meilleure Position', 'Nombre Résultats', 'Période'];
        
        foreach ($queries as $query) {
            $results = $query->results()
                ->with('contact')
                ->where('analyzed_at', '>=', now()->subDays($dateRange))
                ->where('found', true)
                ->get();
                
            $avgPosition = $results->avg('position');
            $bestPosition = $results->min('position');
            
            $csvData[] = [
                $query->name,
                'Résumé',
                $avgPosition ? round($avgPosition, 1) : 'N/A',
                $bestPosition ?: 'N/A',
                $results->count(),
                "{$dateRange} derniers jours",
            ];
        }
        
        $filename = "seo-comparison-" . now()->format('Y-m-d') . '.csv';
        
        return response()->stream(function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}