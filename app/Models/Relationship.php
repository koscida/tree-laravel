<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'label',
        'is_multiple_people',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_multiple_people' => 'boolean',
    ];
}
