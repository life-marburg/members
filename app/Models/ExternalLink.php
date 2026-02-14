<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected static function booted(): void
    {
        static::saved(function () {
            cache()->forget('external_links_active');
        });

        static::deleted(function () {
            cache()->forget('external_links_active');
        });
    }

    public static function getCachedActiveLinks()
    {
        return cache()->remember('external_links_active', 3600, function () {
            return static::active()->ordered()->get();
        });
    }
}
