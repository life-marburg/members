<?php

namespace App\Models;

use App\ManagesInstruments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalData extends Model
{
    use HasFactory;
    use ManagesInstruments;

    protected $fillable = [
        'street',
        'city',
        'zip',
    ];
}
