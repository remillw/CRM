<?php

namespace App\Http\Controllers;

use App\Models\ContactList;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class EmailCampaignController extends Controller
{
    public function index(Request $request): Response
    {
        $campaigns = QueryBuilder::for(EmailCampaign::class)
            ->allowedFilters(['status', 'name'])
            ->allowedSorts(['created_at', 'name', 'sent_at'])
            ->with(['list', 'template'])
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('EmailCampaigns/Index', [
            'campaigns' => $campaigns,
            'filters' => $request->only(['filter', 'sort'])
        ]);
    }

    public function create(): Response
    {
        $lists = ContactList::all(['id', 'name', 'total_contacts']);
        $templates = EmailTemplate::active()->get(['id', 'name', 'subject']);

        return Inertia::render('EmailCampaigns/Create', [
            'lists' => $lists,
            'templates' => $templates
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'list_id' => 'required|exists:contact_lists,id',
            'template_id' => 'required|exists:email_templates,id',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $list = ContactList::find($validated['list_id']);
        
        $campaign = EmailCampaign::create([
            ...$validated,
            'total_recipients' => $list->total_contacts,
            'status' => $validated['scheduled_at'] ? 'scheduled' : 'draft'
        ]);

        return redirect()->route('email-campaigns.show', $campaign)
            ->with('success', 'Campagne email créée !');
    }

    public function show(EmailCampaign $emailCampaign): Response
    {
        $emailCampaign->load(['list', 'template', 'emailSends' => function ($query) {
            $query->latest()->take(20);
        }]);

        $stats = [
            'sent_count' => $emailCampaign->sent_count,
            'open_rate' => $emailCampaign->open_rate,
            'click_rate' => $emailCampaign->click_rate,
            'bounce_rate' => $emailCampaign->bounce_rate,
        ];

        return Inertia::render('EmailCampaigns/Show', [
            'campaign' => $emailCampaign,
            'stats' => $stats,
            'recent_sends' => $emailCampaign->emailSends
        ]);
    }

    public function send(EmailCampaign $emailCampaign)
    {
        if ($emailCampaign->status !== 'draft') {
            return back()->with('error', 'Cette campagne ne peut pas être envoyée.');
        }

        $emailCampaign->update(['status' => 'sending']);

        dispatch(function () use ($emailCampaign) {
            // Job d'envoi d'emails sera créé plus tard
        });

        return back()->with('success', 'Envoi démarré !');
    }

    public function destroy(EmailCampaign $emailCampaign)
    {
        if ($emailCampaign->status === 'sending') {
            return back()->with('error', 'Impossible de supprimer une campagne en cours d\'envoi.');
        }

        $emailCampaign->delete();

        return redirect()->route('email-campaigns.index')
            ->with('success', 'Campagne supprimée !');
    }
}
