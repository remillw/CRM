<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'list_id',
        'segment_id',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(ContactList::class, 'list_id');
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(ContactListSegment::class, 'segment_id');
    }
}
