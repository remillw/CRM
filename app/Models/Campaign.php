<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'activity_type',
        'city',
        'target_count',
        'status',
        'config',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'config' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function emailSends(): HasMany
    {
        return $this->hasMany(EmailSend::class);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_count === 0) {
            return 0;
        }
        return ($this->contacts()->count() / $this->target_count) * 100;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }
}
