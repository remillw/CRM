<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EmailSend extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'campaign_schedule_id',
        'contact_id',
        'tracking_id',
        'template_name',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'unsubscribed_at',
        'follow_up_sent_at',
        'follow_up_type',
        'error_details',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'follow_up_sent_at' => 'datetime',
        'error_details' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($emailSend) {
            if (empty($emailSend->tracking_id)) {
                $emailSend->tracking_id = Str::uuid();
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function markAsOpened(): void
    {
        if (!$this->opened_at) {
            $this->update([
                'opened_at' => now(),
                'status' => 'opened'
            ]);
        }
    }

    public function markAsClicked(): void
    {
        $this->update([
            'clicked_at' => now(),
            'status' => 'clicked'
        ]);
        
        if (!$this->opened_at) {
            $this->markAsOpened();
        }
    }

    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }

    public function scopeClicked($query)
    {
        return $query->whereNotNull('clicked_at');
    }
}
