<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\GoogleMapsScrapingService;
use App\Imports\ContactsImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

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
        return Inertia::render('Campaigns/CreateChoice');
    }

    public function createChoice(): Response
    {
        return Inertia::render('Campaigns/CreateChoice');
    }

    public function createEmpty(): Response
    {
        return Inertia::render('Campaigns/CreateEmpty');
    }

    public function createScraping(): Response
    {
        return Inertia::render('Campaigns/Create');
    }

    public function store(Request $request)
    {
        // Redirect to the choice page (legacy compatibility)
        return redirect()->route('campaigns.create-choice');
    }

    public function storeEmpty(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'activity_type' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $campaign = Campaign::create([
            'name' => $validated['name'],
            'activity_type' => $validated['activity_type'] ?? 'Général',
            'city' => $validated['city'] ?? 'Non définie',
            'target_count' => 0,
            'status' => 'pending',
            'config' => [
                'description' => $validated['description'] ?? null,
                'source' => 'manual',
            ],
        ]);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campagne vide créée avec succès !');
    }

    public function storeScraping(Request $request)
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

    public function createWithImport(): Response
    {
        return Inertia::render('Campaigns/CreateWithImport');
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120', // 5MB max
        ]);

        try {
            // Create campaign with import source
            $campaign = Campaign::create([
                'name' => $validated['name'],
                'activity_type' => 'Import',
                'city' => 'Import',
                'target_count' => 0, // Will be updated after import
                'status' => 'running',
                'config' => [
                    'description' => $validated['description'] ?? null,
                    'source' => 'file_import',
                    'filename' => $request->file('file')->getClientOriginalName(),
                ],
            ]);

            // Process the import
            $import = new ContactsImport($campaign);
            Excel::import($import, $request->file('file'));

            // Update campaign with actual contact count
            $contactCount = $campaign->contacts()->count();
            $campaign->update([
                'target_count' => $contactCount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return redirect()->route('campaigns.show', $campaign)
                ->with('success', "Campagne créée avec succès ! {$contactCount} contacts importés.");

        } catch (\Exception $e) {
            if (isset($campaign)) {
                $campaign->update(['status' => 'failed']);
            }

            return back()->withErrors([
                'file' => 'Erreur lors de l\'import du fichier : ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_contacts.csv"',
        ];

        $columns = [
            'business_name',
            'owner_name',
            'phone',
            'email',
            'website',
            'address',
            'city',
            'postal_code',
            'activity_type',
            'google_rating',
            'review_count',
        ];

        $sampleData = [
            [
                'Pizza Roma',
                'Jean Dupont',
                '0142567890',
                'contact@pizzaroma.fr',
                'www.pizzaroma.fr',
                '123 rue de la République',
                'Paris',
                '75001',
                'Pizzeria',
                '4.5',
                '150',
            ],
            [
                'Le Petit Bistro',
                'Marie Martin',
                '0145678901',
                'info@lepetitbistro.fr',
                'www.lepetitbistro.fr',
                '45 avenue Victor Hugo',
                'Lyon',
                '69002',
                'Restaurant',
                '4.2',
                '89',
            ],
        ];

        $callback = function() use ($columns, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
