<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'template_type',
        'delay_days',
        'conditions',
        'template_id',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function shouldTrigger(EmailSend $emailSend): bool
    {
        $conditions = $this->conditions;
        $daysSinceSent = now()->diffInDays($emailSend->sent_at);

        // Vérifier le délai
        if ($daysSinceSent < $this->delay_days) {
            return false;
        }

        // Vérifier les conditions spécifiques
        foreach ($conditions as $condition => $value) {
            switch ($condition) {
                case 'not_opened':
                    if ($value && $emailSend->opened_at) {
                        return false;
                    }
                    break;
                case 'opened_no_click':
                    if ($value && (!$emailSend->opened_at || $emailSend->clicked_at)) {
                        return false;
                    }
                    break;
                case 'no_response':
                    if ($value && $emailSend->status === 'responded') {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }
}