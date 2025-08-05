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
    ];

    protected $casts = [
        'filters' => 'array',
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
}
