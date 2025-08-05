<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Jobs\AnalyzeSeoPosition;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $query = QueryBuilder::for(Contact::class)
            ->allowedFilters(['business_name', 'email', 'phone'])
            ->allowedSorts(['business_name', 'created_at', 'google_rating'])
            ->with('campaign:id,name,activity_type,city');
            
        // Filtre par campagne si spécifié
        if ($request->filled('campaign')) {
            \Log::info('Filtrage par campagne ID: ' . $request->campaign);
            $query->where('campaign_id', $request->campaign);
        }
            
        $contacts = $query->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Contact::count(),
            'with_email' => Contact::withEmail()->count(),
            'with_website' => Contact::withWebsite()->count(),
            'verified' => Contact::where('is_verified', true)->count(),
        ];

        $campaigns = \App\Models\Campaign::select('id', 'name', 'activity_type', 'city')->get();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'stats' => $stats,
            'campaigns' => $campaigns,
            'filters' => $request->only(['filter', 'sort', 'campaign'])
        ]);
    }

    public function show(Contact $contact): Response
    {
        $contact->load(['campaign', 'lists', 'emailSends.campaign', 'seoResults.seoQuery']);

        // Récupérer les requêtes SEO actives
        $activeSeoQueries = \App\Models\SeoQuery::where('is_active', true)
            ->get()
            ->map(function($query) use ($contact) {
                $latestResult = $contact->seoResults()
                    ->where('seo_query_id', $query->id)
                    ->latest('analyzed_at')
                    ->first();
                
                return [
                    'id' => $query->id,
                    'name' => $query->name,
                    'query' => $query->query,
                    'location' => $query->location,
                    'latest_result' => $latestResult ? [
                        'position' => $latestResult->position,
                        'found' => $latestResult->found,
                        'analyzed_at' => $latestResult->analyzed_at,
                        'url_found' => $latestResult->url_found,
                    ] : null
                ];
            });

        return Inertia::render('Contacts/Show', [
            'contact' => $contact,
            'seoQueries' => $activeSeoQueries
        ]);
    }

    public function edit(Contact $contact): Response
    {
        return Inertia::render('Contacts/Edit', [
            'contact' => $contact
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
        ]);

        $contact->update($validated);

        return back()->with('success', 'Contact mis à jour !');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact supprimé !');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contacts,id'
        ]);

        Contact::whereIn('id', $request->contact_ids)->delete();

        return back()->with('success', count($request->contact_ids) . ' contacts supprimés !');
    }

    public function export(Request $request)
    {
        $contacts = QueryBuilder::for(Contact::class)
            ->allowedFilters(['business_name', 'email', 'phone'])
            ->with('campaign:id,name,activity_type,city')
            ->get();

        $csv = "Entreprise,Téléphone,Email,Site web,Adresse,Note Google,Nb Avis,Campagne,Type Activité,Ville\n";
        
        foreach ($contacts as $contact) {
            $csv .= implode(',', [
                '"' . str_replace('"', '""', $contact->business_name) . '"',
                '"' . ($contact->phone ?? '') . '"',
                '"' . ($contact->email ?? '') . '"',
                '"' . ($contact->website ?? '') . '"',
                '"' . str_replace('"', '""', $contact->address ?? '') . '"',
                $contact->google_rating ?? '',
                $contact->review_count ?? '',
                '"' . ($contact->campaign->name ?? '') . '"',
                '"' . ($contact->campaign->activity_type ?? '') . '"',
                '"' . ($contact->campaign->city ?? '') . '"',
            ]) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="contacts-' . date('Y-m-d') . '.csv"');
    }

    public function toggleSiteGood(Contact $contact)
    {
        $contact->update(['site_good' => !$contact->site_good]);
        
        return back()->with('success', 'Site Good mis à jour !');
    }

    public function toggleCanCommand(Contact $contact)
    {
        $contact->update(['can_command' => !$contact->can_command]);
        
        return back()->with('success', 'Can Command mis à jour !');
    }

    public function analyzeSeo(Contact $contact)
    {
        if (!$contact->website) {
            return back()->with('error', 'Ce contact n\'a pas de site web à analyser.');
        }

        // Lancer le job d'analyse SEO
        AnalyzeSeoPosition::dispatch($contact);
        
        return back()->with('success', 'Analyse SEO lancée pour ' . $contact->business_name . '. Les résultats seront disponibles dans quelques minutes.');
    }

    public function bulkAnalyzeSeo(Request $request)
    {
        $request->validate([
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:contacts,id'
        ]);

        $contacts = Contact::whereIn('id', $request->contact_ids)
            ->whereNotNull('website')
            ->get();

        $launched = 0;
        foreach ($contacts as $contact) {
            AnalyzeSeoPosition::dispatch($contact);
            $launched++;
        }

        return back()->with('success', "Analyse SEO lancée pour {$launched} contacts avec site web.");
    }

    public function analyzeCampaignSeo(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id'
        ]);

        $contacts = Contact::where('campaign_id', $request->campaign_id)
            ->whereNotNull('website')
            ->get();

        $launched = 0;
        foreach ($contacts as $contact) {
            AnalyzeSeoPosition::dispatch($contact);
            $launched++;
        }

        return back()->with('success', "Analyse SEO lancée pour {$launched} contacts de cette campagne avec site web.");
    }
}
