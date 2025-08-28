<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactList;
use App\Models\ContactListSegment;
use App\Models\ContactListItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class ContactListController extends Controller
{
    public function index(Request $request): Response
    {
        $lists = QueryBuilder::for(ContactList::class)
            ->allowedFilters(['name'])
            ->allowedSorts(['created_at', 'name', 'total_contacts'])
            ->withCount(['contacts', 'segments'])
            ->with('syncCampaign:id,name')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_lists' => ContactList::count(),
            'active_lists' => ContactList::where('status', 'active')->count(),
            'total_contacts_in_lists' => \App\Models\ContactListItem::count(),
        ];

        return Inertia::render('ContactLists/IndexModern', [
            'lists' => $lists,
            'stats' => $stats,
            'filters' => $request->only(['filter', 'sort'])
        ]);
    }

    public function create(): Response
    {
        $campaigns = \App\Models\Campaign::withCount('contacts')->get(['id', 'name', 'activity_type', 'city']);
        
        return Inertia::render('ContactLists/Create', [
            'campaigns' => $campaigns
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'campaign_ids' => 'required|array',
            'campaign_ids.*' => 'exists:campaigns,id',
            'website_filter' => 'required|in:all,with_website,without_website,good_website,bad_website',
            'command_filter' => 'required|in:all,can_command,cannot_command',
            'seo_filter' => 'required|in:all,top_10,top_20,poor_ranking,not_analyzed',
            'email_filter' => 'required|in:all,with_email,without_email',
            'rating_filter' => 'required|in:all,excellent,good,poor,no_rating',
            'verified_filter' => 'required|in:all,verified,not_verified',
        ]);

        // Créer la liste
        $list = ContactList::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'filters' => [
                'website_filter' => $validated['website_filter'],
                'command_filter' => $validated['command_filter'],
                'seo_filter' => $validated['seo_filter'],
                'email_filter' => $validated['email_filter'],
                'rating_filter' => $validated['rating_filter'],
                'verified_filter' => $validated['verified_filter'],
                'campaign_ids' => $validated['campaign_ids']
            ],
        ]);

        // Construire la query avec tous les filtres
        $query = Contact::whereIn('campaign_id', $validated['campaign_ids']);

        // Appliquer les filtres website
        switch ($validated['website_filter']) {
            case 'with_website':
                $query->whereNotNull('website');
                break;
            case 'without_website':
                $query->whereNull('website');
                break;
            case 'good_website':
                $query->where('site_good', true);
                break;
            case 'bad_website':
                $query->where('site_good', false);
                break;
        }

        // Appliquer les filtres command
        switch ($validated['command_filter']) {
            case 'can_command':
                $query->where('can_command', true);
                break;
            case 'cannot_command':
                $query->where('can_command', false);
                break;
        }

        // Appliquer les filtres SEO
        switch ($validated['seo_filter']) {
            case 'top_10':
                $query->whereBetween('seo_position', [1, 10]);
                break;
            case 'top_20':
                $query->whereBetween('seo_position', [1, 20]);
                break;
            case 'poor_ranking':
                $query->where('seo_position', '>', 50);
                break;
            case 'not_analyzed':
                $query->whereNull('seo_analyzed_at');
                break;
        }

        // Appliquer les filtres email
        switch ($validated['email_filter']) {
            case 'with_email':
                $query->whereNotNull('email');
                break;
            case 'without_email':
                $query->whereNull('email');
                break;
        }

        // Appliquer les filtres rating
        switch ($validated['rating_filter']) {
            case 'excellent':
                $query->where('google_rating', '>=', 4.5);
                break;
            case 'good':
                $query->where('google_rating', '>=', 4.0);
                break;
            case 'poor':
                $query->where('google_rating', '<', 3.0);
                break;
            case 'no_rating':
                $query->whereNull('google_rating');
                break;
        }

        // Appliquer les filtres verified
        switch ($validated['verified_filter']) {
            case 'verified':
                $query->where('is_verified', true);
                break;
            case 'not_verified':
                $query->where('is_verified', false);
                break;
        }

        $contacts = $query->get();

        // Ajouter les contacts à la liste
        foreach ($contacts as $contact) {
            ContactListItem::create([
                'contact_id' => $contact->id,
                'list_id' => $list->id,
            ]);
        }

        return redirect()->route('contact-lists.show', $list)
            ->with('success', 'Liste créée avec ' . $contacts->count() . ' contacts !');
    }

    public function show(ContactList $contactList): Response
    {
        $contactList->load(['segments']);
        
        $contacts = QueryBuilder::for($contactList->contacts())
            ->allowedFilters(['business_name', 'email', 'phone'])
            ->allowedSorts(['business_name', 'created_at'])
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => $contactList->total_contacts,
            'with_email' => $contactList->contacts()->withEmail()->count(),
            'with_website' => $contactList->contacts()->withWebsite()->count(),
            'without_website' => $contactList->contacts()->withoutWebsite()->count(),
        ];

        return Inertia::render('ContactLists/Show', [
            'list' => $contactList,
            'contacts' => $contacts,
            'segments' => $contactList->segments,
            'stats' => $stats
        ]);
    }

    public function edit(ContactList $contactList): Response
    {
        $campaigns = \App\Models\Campaign::withCount('contacts')->get(['id', 'name', 'activity_type', 'city']);
        
        return Inertia::render('ContactLists/Edit', [
            'contactList' => $contactList,
            'campaigns' => $campaigns
        ]);
    }

    public function update(Request $request, ContactList $contactList)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
                'campaign_ids' => 'required|array',
                'campaign_ids.*' => 'exists:campaigns,id',
                'website_filter' => 'required|in:all,with_website,without_website,good_website,bad_website',
                'command_filter' => 'required|in:all,can_command,cannot_command',
                'seo_filter' => 'required|in:all,top_10,top_20,poor_ranking,not_analyzed',
                'email_filter' => 'required|in:all,with_email,without_email',
                'rating_filter' => 'required|in:all,excellent,good,poor,no_rating',
                'verified_filter' => 'required|in:all,verified,not_verified',
            ]);

            // Mettre à jour la liste
            $contactList->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'filters' => [
                    'website_filter' => $validated['website_filter'],
                    'command_filter' => $validated['command_filter'],
                    'seo_filter' => $validated['seo_filter'],
                    'email_filter' => $validated['email_filter'],
                    'rating_filter' => $validated['rating_filter'],
                    'verified_filter' => $validated['verified_filter'],
                    'campaign_ids' => $validated['campaign_ids']
                ],
            ]);

            // Reconstruire la liste avec les nouveaux filtres
            $this->rebuildContactList($contactList, $validated);
            
            // Mettre à jour le nombre de contacts
            $contactList->update(['total_contacts' => $contactList->contacts()->count()]);

            return redirect()->route('contact-lists.index')
                ->with('success', 'Liste mise à jour avec succès !');
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(ContactList $contactList)
    {
        $contactList->delete();

        return redirect()->route('contact-lists.index')
            ->with('success', 'Liste supprimée !');
    }

    public function createSegment(Request $request, ContactList $contactList)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'conditions' => 'required|array',
        ]);

        $segment = ContactListSegment::create([
            'list_id' => $contactList->id,
            'name' => $validated['name'],
            'conditions' => $validated['conditions'],
        ]);

        $matchingContacts = $segment->getMatchingContacts()->count();
        $segment->update(['contact_count' => $matchingContacts]);

        return back()->with('success', "Segment créé avec {$matchingContacts} contacts !");
    }

    private function createDefaultSegments(ContactList $list): void
    {
        $segments = [
            [
                'name' => 'Sans site web',
                'conditions' => [['field' => 'has_website', 'value' => false]]
            ],
            [
                'name' => 'Sans email',
                'conditions' => [['field' => 'has_email', 'value' => false]]
            ],
            [
                'name' => 'Bien notés (4+)',
                'conditions' => [['field' => 'google_rating', 'operator' => '>=', 'value' => 4.0]]
            ]
        ];

        foreach ($segments as $segmentData) {
            $segment = ContactListSegment::create([
                'list_id' => $list->id,
                'name' => $segmentData['name'],
                'conditions' => $segmentData['conditions'],
            ]);

            $matchingContacts = $segment->getMatchingContacts()->count();
            $segment->update(['contact_count' => $matchingContacts]);
        }
    }

    private function rebuildContactList(ContactList $contactList, array $filters): void
    {
        // Supprimer tous les contacts existants de la liste
        ContactListItem::where('list_id', $contactList->id)->delete();

        // Construire la query avec tous les filtres
        $query = Contact::whereIn('campaign_id', $filters['campaign_ids']);

        // Appliquer les filtres website
        switch ($filters['website_filter']) {
            case 'with_website':
                $query->whereNotNull('website');
                break;
            case 'without_website':
                $query->whereNull('website');
                break;
            case 'good_website':
                $query->where('site_good', true);
                break;
            case 'bad_website':
                $query->where('site_good', false);
                break;
        }

        // Appliquer les filtres command
        switch ($filters['command_filter']) {
            case 'can_command':
                $query->where('can_command', true);
                break;
            case 'cannot_command':
                $query->where('can_command', false);
                break;
        }

        // Appliquer les filtres SEO
        switch ($filters['seo_filter']) {
            case 'top_10':
                $query->whereBetween('seo_position', [1, 10]);
                break;
            case 'top_20':
                $query->whereBetween('seo_position', [1, 20]);
                break;
            case 'poor_ranking':
                $query->where('seo_position', '>', 50);
                break;
            case 'not_analyzed':
                $query->whereNull('seo_analyzed_at');
                break;
        }

        // Appliquer les filtres email
        switch ($filters['email_filter']) {
            case 'with_email':
                $query->whereNotNull('email');
                break;
            case 'without_email':
                $query->whereNull('email');
                break;
        }

        // Appliquer les filtres rating
        switch ($filters['rating_filter']) {
            case 'excellent':
                $query->where('google_rating', '>=', 4.5);
                break;
            case 'good':
                $query->where('google_rating', '>=', 4.0);
                break;
            case 'poor':
                $query->where('google_rating', '<', 3.0);
                break;
            case 'no_rating':
                $query->whereNull('google_rating');
                break;
        }

        // Appliquer les filtres verified
        switch ($filters['verified_filter']) {
            case 'verified':
                $query->where('is_verified', true);
                break;
            case 'not_verified':
                $query->where('is_verified', false);
                break;
        }

        $contacts = $query->get();

        // Ajouter les contacts à la liste
        foreach ($contacts as $contact) {
            ContactListItem::create([
                'contact_id' => $contact->id,
                'list_id' => $contactList->id,
            ]);
        }
    }

    public function sync(ContactList $contactList)
    {
        try {
            $contactList->syncContacts();
            
            // Recharger pour avoir le nombre de contacts mis à jour
            $contactList->refresh();
            
            return back()->with('success', "Synchronisation réussie ! {$contactList->total_contacts} contacts synchronisés.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la synchronisation : ' . $e->getMessage());
        }
    }

}
