<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'area'])]
class Neighborhood extends Model
{
    public $timestamps = false;

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subscriptions')
            ->withPivot('created_at');
    }
}
