<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\GoogleMapsScrapingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class CampaignController extends Controller
{
    public function __construct(
        protected GoogleMapsScrapingService $scrapingService
    ) {}

    public function index(Request $request): Response
    {
        $campaigns = QueryBuilder::for(Campaign::class)
            ->allowedFilters(['status', 'activity_type', 'city'])
            ->allowedSorts(['created_at', 'name', 'status'])
            ->withCount('contacts')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
            'filters' => $request->only(['filter', 'sort'])
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Campaigns/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'activity_type' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'target_count' => 'required|integer|min:1|max:1000',
        ]);

        $campaign = Campaign::create($validated);

        \App\Jobs\ProcessCampaignScraping::dispatch($campaign);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campagne créée ! Le scraping est en cours en arrière-plan...');
    }

    public function show(Campaign $campaign): Response
    {
        $campaign->load(['contacts' => function ($query) {
            $query->latest()->take(10);
        }]);

        $stats = [
            'total_contacts' => $campaign->contacts()->count(),
            'with_email' => $campaign->contacts()->withEmail()->count(),
            'with_website' => $campaign->contacts()->withWebsite()->count(),
            'without_website' => $campaign->contacts()->withoutWebsite()->count(),
            'progress' => $campaign->progress_percentage,
        ];

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
            'stats' => $stats,
            'recent_contacts' => $campaign->contacts
        ]);
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')
            ->with('success', 'Campagne supprimée !');
    }

    public function restart(Campaign $campaign)
    {
        if ($campaign->status === 'failed') {
            $campaign->update(['status' => 'pending']);
            
            \App\Jobs\ProcessCampaignScraping::dispatch($campaign);

            return back()->with('success', 'Scraping relancé !');
        }

        return back()->with('error', 'Impossible de relancer cette campagne.');
    }
}
