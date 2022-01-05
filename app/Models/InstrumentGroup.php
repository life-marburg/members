<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentGroup extends Model
{
    use HasFactory;

    public function instruments(): HasMany
    {
        return $this->hasMany(Instrument::class);
    }
}
