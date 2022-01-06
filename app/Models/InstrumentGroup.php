<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperInstrumentGroup
 */
class InstrumentGroup extends Model
{
    use HasFactory;

    public function instruments(): HasMany
    {
        return $this->hasMany(Instrument::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_instrument_group');
    }
}
