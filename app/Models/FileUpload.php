<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'filename',
        'filepath',
        'record_type',
    ];
}
