<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileRowLog extends Model
{
    /**
     * @var string
     */
    protected $table = 'file_rows_log';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'file_upload_id',
        'status',
        'message',
    ];

    public function fileUpload(): BelongsTo
    {
        return $this->belongsTo(FileUpload::class);
    }
}
