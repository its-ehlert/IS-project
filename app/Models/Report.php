<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'neighborhood_id', 'status', 'notes'])]
class Report extends Model
{
    const CREATED_AT = 'reported_at';
    const UPDATED_AT = null;

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function scopeNeighborhood(Builder $query, ?int $neighborhoodId): Builder
    {
        if (! $neighborhoodId) {
            return $query;
        }

        return $query->where('neighborhood_id', $neighborhoodId);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeVerifiedFilter(Builder $query, ?string $verified): Builder
    {
        if ($verified === null || $verified === '') {
            return $query;
        }

        return $query->where('verified', $verified === 'true' || $verified === '1');
    }

    public function scopeSearchTerm(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->whereHas('neighborhood', fn (Builder $n) => $n->where('name', 'like', "%{$term}%"))
                ->orWhere('notes', 'like', "%{$term}%")
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$term}%"));
        });
    }
}
