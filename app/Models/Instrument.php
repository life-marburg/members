<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperInstrument
 */
class Instrument extends Model
{
    use HasFactory;

    public function instrumentGroup(): BelongsTo
    {
        return $this->belongsTo(InstrumentGroup::class);
    }

    public function getFileTitleAttribute(): string
    {
        return str_replace(' ', '', $this->title);
    }
}
