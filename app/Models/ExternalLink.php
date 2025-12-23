<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ExternalLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'show_external_icon',
        'position',
        'is_active',
        'target',
    ];

    protected $casts = [
        'show_external_icon' => 'boolean',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }
}
