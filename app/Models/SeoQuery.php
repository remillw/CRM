<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'query',
        'location',
        'max_pages',
        'frequency',
        'is_active',
        'is_one_time',
        'executed_at',
        'target_campaigns',
        'description',
        'last_analyzed_at',
        'next_analysis_at',
    ];

    protected $casts = [
        'target_campaigns' => 'array',
        'is_active' => 'boolean',
        'is_one_time' => 'boolean',
        'executed_at' => 'datetime',
        'last_analyzed_at' => 'datetime',
        'next_analysis_at' => 'datetime',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(SeoResult::class);
    }

    protected static function booted(): void
    {
        // Supprimer tous les résultats associés quand une requête SEO est supprimée
        static::deleting(function (SeoQuery $seoQuery) {
            $seoQuery->results()->delete();
        });
    }

    public function latestResults(): HasMany
    {
        return $this->hasMany(SeoResult::class)->latest('analyzed_at');
    }

    public function getTargetContacts()
    {
        $query = Contact::query();
        
        if ($this->target_campaigns) {
            $query->whereIn('campaign_id', $this->target_campaigns);
        }
        
        return $query->whereNotNull('website');
    }

    public function scheduleNextAnalysis(): void
    {
        // Ne pas programmer de prochaine analyse pour les requêtes one-time
        if ($this->is_one_time) {
            $this->update(['next_analysis_at' => null]);
            return;
        }

        $next = match($this->frequency) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            default => now()->addWeek()
        };
        
        $this->update(['next_analysis_at' => $next]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueForAnalysis($query)
    {
        return $query->active()
            ->where(function($q) {
                // Requêtes récurrentes dues
                $q->where('is_one_time', false)
                  ->where(function($subq) {
                      $subq->whereNull('next_analysis_at')
                           ->orWhere('next_analysis_at', '<=', now());
                  });
            });
    }

    public function canExecute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Pour les requêtes one-time, vérifier qu'elles n'ont pas déjà été exécutées
        if ($this->is_one_time) {
            return is_null($this->executed_at);
        }

        // Pour les requêtes récurrentes, utiliser la logique normale
        return is_null($this->next_analysis_at) || $this->next_analysis_at <= now();
    }

    public function markAsExecuted(): void
    {
        if ($this->is_one_time) {
            $this->update([
                'executed_at' => now(),
                'last_analyzed_at' => now()
            ]);
        } else {
            $this->update(['last_analyzed_at' => now()]);
            $this->scheduleNextAnalysis();
        }
    }

    public function canRelaunch(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Les requêtes one-time peuvent être relancées en réinitialisant executed_at
        if ($this->is_one_time && $this->executed_at) {
            return true;
        }

        // Les requêtes récurrentes peuvent toujours être relancées
        return !$this->is_one_time;
    }

    public function relaunch(): void
    {
        if ($this->is_one_time && $this->executed_at) {
            // Réinitialiser l'exécution pour les requêtes one-time
            $this->update([
                'executed_at' => null,
                'last_analyzed_at' => null
            ]);
        } else {
            // Pour les requêtes récurrentes, juste mettre à jour la dernière analyse
            $this->update(['last_analyzed_at' => now()]);
        }
    }
}
