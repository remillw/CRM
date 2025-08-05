<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'seo_query_id',
        'contact_id',
        'query_used',
        'position',
        'url_found',
        'serp_data',
        'total_results',
        'competitors',
        'found',
        'analyzed_at',
    ];

    protected $casts = [
        'serp_data' => 'array',
        'competitors' => 'array',
        'found' => 'boolean',
        'analyzed_at' => 'datetime',
    ];

    public function seoQuery(): BelongsTo
    {
        return $this->belongsTo(SeoQuery::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function getPositionEvolutionAttribute(): array
    {
        $previous = static::where('contact_id', $this->contact_id)
            ->where('seo_query_id', $this->seo_query_id)
            ->where('analyzed_at', '<', $this->analyzed_at)
            ->latest('analyzed_at')
            ->first();

        if (!$previous) {
            return ['status' => 'new', 'change' => 0];
        }

        if ($this->position && $previous->position) {
            $change = $previous->position - $this->position;
            
            if ($change > 0) {
                return ['status' => 'up', 'change' => $change];
            } elseif ($change < 0) {
                return ['status' => 'down', 'change' => abs($change)];
            } else {
                return ['status' => 'stable', 'change' => 0];
            }
        }

        if ($this->position && !$previous->position) {
            return ['status' => 'entered', 'change' => 0];
        }

        if (!$this->position && $previous->position) {
            return ['status' => 'lost', 'change' => 0];
        }

        return ['status' => 'stable', 'change' => 0];
    }

    public function scopeForQuery($query, $seoQueryId)
    {
        return $query->where('seo_query_id', $seoQueryId);
    }

    public function scopeForContact($query, $contactId)
    {
        return $query->where('contact_id', $contactId);
    }

    public function scopeFound($query)
    {
        return $query->where('found', true);
    }

    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('analyzed_at', [$start, $end]);
    }
}
