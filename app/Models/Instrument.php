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

    protected $casts = [
        'aliases' => 'array',
    ];

    public function instrumentGroup(): BelongsTo
    {
        return $this->belongsTo(InstrumentGroup::class);
    }

    public function getFileTitleAttribute(): string
    {
        return str_replace(' ', '', $this->title);
    }

    public function getTitleWithAliasAttribute(): array
    {
        return [
            $this->file_title,
            ...($this->aliases ?? []),
        ];
    }
}
