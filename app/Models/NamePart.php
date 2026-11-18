<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NamePart extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'label',
    ];
}
