<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'song_id',
        'instrument_id',
        'part_number',
        'variant',
        'file_path',
    ];

    protected $casts = [
        'part_number' => 'integer',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(Instrument::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        if ($this->part_number === null && $this->variant) {
            return $this->variant;
        }

        $title = $this->part_number.'. Stimme';

        if ($this->variant) {
            $title .= ' '.$this->variant;
        }

        return $title;
    }
}
