<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surname extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'surname',
        'canonical_surname_id',
    ];

    public function canonicalSurname(): BelongsTo
    {
        return $this->belongsTo(self::class, 'canonical_surname_id');
    }

    public function fileConnections(): HasMany
    {
        return $this->hasMany(FileConnection::class);
    }
}
