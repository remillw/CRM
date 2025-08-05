<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsSend extends Model
{
    protected $fillable = [
        'sms_campaign_schedule_id',
        'contact_id',
        'phone',
        'message',
        'twilio_message_id',
        'status',
        'sent_at',
        'delivered_at',
        'failed_at',
        'error_message',
        'cost',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'cost' => 'decimal:4',
    ];

    public function smsCampaignSchedule(): BelongsTo
    {
        return $this->belongsTo(SmsCampaignSchedule::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
