<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileMapping extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'file_upload_id',
        'column_idx',
        'map_kind',
        'relationship_id',
        'name_part_id',
        'milestone_id',
        'date_part_id',
        'record_label_id',
        'source_part_id',
    ];
}
