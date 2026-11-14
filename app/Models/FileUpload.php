<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileUpload extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'filename',
        'filepath',
        'record_type',
        'header_rows',
        'is_parsed',
        'parsed_date',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_parsed' => 'boolean',
        'parsed_date' => 'datetime',
    ];

    public function fileRows(): HasMany
    {
        return $this->hasMany(FileRow::class);
    }

    public function fileRowLogs(): HasMany
    {
        return $this->hasMany(FileRowLog::class);
    }
}
