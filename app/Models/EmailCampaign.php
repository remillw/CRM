<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'template_id',
        'name',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function list(): BelongsTo
    {
        return $this->belongsTo(ContactList::class, 'list_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function emailSends(): HasMany
    {
        return $this->hasMany(EmailSend::class, 'campaign_id');
    }

    public function getOpenRateAttribute(): float
    {
        $opened = $this->emailSends()->whereNotNull('opened_at')->count();
        return $this->sent_count > 0 ? ($opened / $this->sent_count) * 100 : 0;
    }

    public function getClickRateAttribute(): float
    {
        $clicked = $this->emailSends()->whereNotNull('clicked_at')->count();
        return $this->sent_count > 0 ? ($clicked / $this->sent_count) * 100 : 0;
    }

    public function getBounceRateAttribute(): float
    {
        $bounced = $this->emailSends()->whereNotNull('bounced_at')->count();
        return $this->sent_count > 0 ? ($bounced / $this->sent_count) * 100 : 0;
    }
}
