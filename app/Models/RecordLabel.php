<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordLabel extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'label',
        'data_format',
    ];
}
