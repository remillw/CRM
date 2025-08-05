<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactListSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'name',
        'conditions',
        'contact_count',
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(ContactList::class, 'list_id');
    }

    public function getMatchingContacts()
    {
        $query = $this->list->contacts();
        
        foreach ($this->conditions as $condition) {
            switch ($condition['field']) {
                case 'has_website':
                    if ($condition['value']) {
                        $query->whereNotNull('website');
                    } else {
                        $query->whereNull('website');
                    }
                    break;
                case 'has_email':
                    if ($condition['value']) {
                        $query->whereNotNull('email');
                    } else {
                        $query->whereNull('email');
                    }
                    break;
                case 'google_rating':
                    $query->where('google_rating', $condition['operator'], $condition['value']);
                    break;
            }
        }
        
        return $query;
    }
}
