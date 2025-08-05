<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsCampaignSchedule extends Model
{
    protected $fillable = [
        'name',
        'sms_template_id',
        'contact_list_ids',
        'filters',
        'scheduled_at',
        'is_test',
        'total_recipients',
        'status',
        'sent_at',
        'notes',
    ];

    protected $casts = [
        'contact_list_ids' => 'array',
        'filters' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'is_test' => 'boolean',
    ];

    public function smsTemplate(): BelongsTo
    {
        return $this->belongsTo(SmsTemplate::class);
    }

    public function smsSends(): HasMany
    {
        return $this->hasMany(SmsSend::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeTest($query)
    {
        return $query->where('is_test', true);
    }

    public function scopeProduction($query)
    {
        return $query->where('is_test', false);
    }
}
