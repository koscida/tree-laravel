<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRow extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'file_upload_id',
        'row_number',
        'is_header',
        'data',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'is_header' => 'boolean',
    ];

    public function fileUpload(): BelongsTo
    {
        return $this->belongsTo(FileUpload::class);
    }
}
