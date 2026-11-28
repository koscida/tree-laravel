<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileConnection extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'file_row_id',
        'file_mapping_id',
        'match_method',
        'connection_kind',
        'surname_id',
    ];

    public function fileRow(): BelongsTo
    {
        return $this->belongsTo(FileRow::class);
    }

    public function fileMapping(): BelongsTo
    {
        return $this->belongsTo(FileMapping::class);
    }

    public function surname(): BelongsTo
    {
        return $this->belongsTo(Surname::class);
    }
}
