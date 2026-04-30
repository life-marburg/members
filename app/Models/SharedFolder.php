<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedFolder extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'group_id', 'is_public'];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
