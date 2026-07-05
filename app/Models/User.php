<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'email', 'password_hash', 'role', 'status', 'neighborhood_id'])]
#[Hidden(['password_hash'])]
class User extends Authenticatable
{
    const UPDATED_AT = null;

    /**
     * Laravel's Auth/Hash facades authenticate against this column instead
     * of the default "password", matching the existing database schema.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Neighborhood::class, 'subscriptions')
            ->withPivot('created_at');
    }

    public function scopeRoleFilter(Builder $query, ?string $role): Builder
    {
        return $role ? $query->where('role', $role) : $query;
    }

    public function scopeStatusFilter(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearchTerm(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
        });
    }
}
