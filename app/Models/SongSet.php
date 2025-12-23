<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SongSet extends Model
{
    use HasFactory;

    protected $fillable = ['title'];

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'song_set_song')
            ->withPivot('position')
            ->orderByPivot('position');
    }
}
