<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'label',
    ];
}
