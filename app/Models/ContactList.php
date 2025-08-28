<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'total_contacts',
        'filters',
        'auto_sync',
        'sync_campaign_id',
        'sync_criteria',
        'last_synced_at',
        'synced_contacts_count',
    ];

    protected $casts = [
        'filters' => 'array',
        'sync_criteria' => 'array',
        'last_synced_at' => 'datetime',
        'auto_sync' => 'boolean',
    ];

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_list_items', 'list_id', 'contact_id');
    }

    public function segments(): HasMany
    {
        return $this->hasMany(ContactListSegment::class, 'list_id');
    }

    public function emailCampaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'list_id');
    }

    public function updateContactCount(): void
    {
        $this->update(['total_contacts' => $this->contacts()->count()]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContactListItem::class, 'list_id');
    }

    public function syncCampaign()
    {
        return $this->belongsTo(Campaign::class, 'sync_campaign_id');
    }

    public function syncContacts(): void
    {
        if (!$this->filters || !isset($this->filters['campaign_ids'])) {
            return;
        }

        // Utiliser les filtres existants de la liste
        $filters = $this->filters;
        $campaignIds = $filters['campaign_ids'];
        
        // Construire la query avec les mêmes filtres que lors de la création
        $query = Contact::whereIn('campaign_id', $campaignIds);

        // Appliquer les filtres website
        switch ($filters['website_filter'] ?? 'all') {
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
        switch ($filters['command_filter'] ?? 'all') {
            case 'can_command':
                $query->where('can_command', true);
                break;
            case 'cannot_command':
                $query->where('can_command', false);
                break;
        }

        // Appliquer les filtres SEO
        switch ($filters['seo_filter'] ?? 'all') {
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
        switch ($filters['email_filter'] ?? 'all') {
            case 'with_email':
                $query->whereNotNull('email');
                break;
            case 'without_email':
                $query->whereNull('email');
                break;
        }

        // Appliquer les filtres rating
        switch ($filters['rating_filter'] ?? 'all') {
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
        switch ($filters['verified_filter'] ?? 'all') {
            case 'verified':
                $query->where('is_verified', true);
                break;
            case 'not_verified':
                $query->where('is_verified', false);
                break;
        }
        
        // Récupérer les contacts correspondants
        $contacts = $query->get();
        
        // Vider la liste actuelle
        $this->contacts()->detach();
        
        // Ajouter les nouveaux contacts
        if ($contacts->isNotEmpty()) {
            $this->contacts()->attach($contacts->pluck('id'));
        }
        
        // Mettre à jour les métadonnées
        $this->update([
            'total_contacts' => $contacts->count(),
            'last_synced_at' => now(),
        ]);
    }
}
