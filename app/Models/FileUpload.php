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
        'is_mapped',
        'mapped_date',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_parsed' => 'boolean',
        'parsed_date' => 'datetime',
        'is_mapped' => 'boolean',
        'mapped_date' => 'datetime',
    ];

    public function fileRows(): HasMany
    {
        return $this->hasMany(FileRow::class);
    }

    public function fileRowLogs(): HasMany
    {
        return $this->hasMany(FileRowLog::class);
    }

    public function fileMappings(): HasMany
    {
        return $this->hasMany(FileMapping::class);
    }

    /**
     * Return the upload for a valid id, or the message explaining why it is invalid.
     */
    public static function findValid(string $fileUploadId): FileUpload|string
    {
        if (! is_numeric($fileUploadId)) {
            return 'The file upload id must be numeric.';
        }

        if (! self::isValidId($fileUploadId)) {
            return 'The file upload id format must be valid.';
        }

        $file = self::find((int) $fileUploadId);

        if ($file === null) {
            return "The file upload id {$fileUploadId} did not match an existing file upload.";
        }

        return $file;
    }

    /**
     * Ids are plain positive integers that fit a signed 64-bit primary key.
     */
    private static function isValidId(string $value): bool
    {
        if (preg_match('/^[0-9]+$/', $value) !== 1) {
            return false;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return true;
        }

        $max = (string) PHP_INT_MAX;

        if (strlen($digits) !== strlen($max)) {
            return strlen($digits) < strlen($max);
        }

        return strcmp($digits, $max) <= 0;
    }
}
