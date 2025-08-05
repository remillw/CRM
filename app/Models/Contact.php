<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
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
        'is_verified',
        'site_good',
        'can_command',
        'notes',
        'seo_position',
        'seo_data',
        'seo_analyzed_at',
        'additional_data',
        'scraped_at',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'scraped_at' => 'datetime',
        'google_rating' => 'decimal:1',
        'is_verified' => 'boolean',
        'site_good' => 'boolean',
        'can_command' => 'boolean',
        'seo_data' => 'array',
        'seo_analyzed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(ContactList::class, 'contact_list_items', 'contact_id', 'list_id');
    }

    public function contactListItems(): HasMany
    {
        return $this->hasMany(ContactListItem::class);
    }

    public function seoResults(): HasMany
    {
        return $this->hasMany(\App\Models\SeoResult::class);
    }

    public function emailSends(): HasMany
    {
        return $this->hasMany(EmailSend::class);
    }

    public function scopeWithEmail($query)
    {
        return $query->whereNotNull('email');
    }

    public function scopeWithWebsite($query)
    {
        return $query->whereNotNull('website');
    }

    public function scopeWithoutWebsite($query)
    {
        return $query->whereNull('website');
    }

    public function getHasWebsiteAttribute(): bool
    {
        return !empty($this->website);
    }

    public function getHasEmailAttribute(): bool
    {
        return !empty($this->email);
    }
}
