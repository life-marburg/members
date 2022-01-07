<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPersonalData
 */
class PersonalData extends Model
{
    use HasFactory;

    protected $fillable = [
        'street',
        'city',
        'zip',
        'phone',
        'mobile_phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
