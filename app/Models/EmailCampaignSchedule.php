<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailCampaignSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'template_id',
        'contact_list_ids',
        'campaign_ids',
        'scheduled_at',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'started_at',
        'completed_at',
        'send_options',
        'notes',
        'is_test',
    ];

    protected $attributes = [
        'status' => 'scheduled',
        'sent_count' => 0,
        'failed_count' => 0,
    ];

    protected $casts = [
        'contact_list_ids' => 'array',
        'campaign_ids' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'send_options' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function emailSends(): HasMany
    {
        return $this->hasMany(EmailSend::class, 'campaign_schedule_id');
    }

    public function getTargetContacts()
    {
        $query = Contact::query();

        // Filtrer par listes de contacts
        if (!empty($this->contact_list_ids)) {
            $query->whereHas('contactListItems', function($q) {
                $q->whereIn('list_id', $this->contact_list_ids);
            });
        }

        // Filtrer par campagnes si spécifié
        if (!empty($this->campaign_ids)) {
            $query->whereIn('campaign_id', $this->campaign_ids);
        }

        // S'assurer qu'on a des emails valides
        $query->whereNotNull('email')
              ->where('email', '!=', '');

        return $query;
    }

    public function calculateTotalRecipients(): int
    {
        return $this->getTargetContacts()->count();
    }

    public function canBeSent(): bool
    {
        // Pour les tests, on peut toujours envoyer
        if ($this->is_test) {
            return $this->total_recipients > 0;
        }
        
        return $this->status === 'scheduled' && 
               $this->scheduled_at <= now() &&
               $this->total_recipients > 0;
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'sending',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'sent',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }

    public function incrementSentCount(): void
    {
        $this->increment('sent_count');
    }

    public function incrementFailedCount(): void
    {
        $this->increment('failed_count');
    }

    public function getProgressPercentage(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round(($this->sent_count / $this->total_recipients) * 100, 1);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '<=', now());
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['scheduled', 'sending']);
    }
}