<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'is_new'];

    public function sheets(): HasMany
    {
        return $this->hasMany(Sheet::class);
    }

    public function songSets(): BelongsToMany
    {
        return $this->belongsToMany(SongSet::class, 'song_set_song')
            ->withPivot('position');
    }
}
