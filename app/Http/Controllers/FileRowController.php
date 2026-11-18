<?php

namespace App\Http\Controllers;

use App\Models\FileRow;
use App\Models\FileRowLog;
use App\Models\FileUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class FileRowController extends Controller
{
    public function index(string $fileUploadId): View|RedirectResponse
    {
        $file = $this->findFile($fileUploadId);

        if ($file instanceof RedirectResponse) {
            return $file;
        }

        $head = $this->readCsvHead($file->filepath, 50);

        if ($head === null) {
            return redirect()
                ->route('files.index')
                ->with('error', 'The CSV file could not be found.');
        }

        [$rows, $totalCsvRows] = $head;

        $fileRows = FileRow::where('file_upload_id', $file->id)
            ->orderBy('row_number')
            ->paginate(25);

        $logs = FileRowLog::where('file_upload_id', $file->id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('file_rows.index', [
            'file' => $file,
            'fileRows' => $fileRows,
            'rows' => $rows,
            'totalCsvRows' => $totalCsvRows,
            'totalFileRows' => $fileRows->total(),
            'fileRowHeaderCount' => FileRow::where('file_upload_id', $file->id)
                ->where('is_header', true)
                ->count(),
            'logsByUuid' => $logs->isEmpty() ? [] : $logs->groupBy('uuid'),
        ]);
    }

    public function store(Request $request, string $fileUploadId): RedirectResponse
    {
        $file = $this->findFile($fileUploadId);

        if ($file instanceof RedirectResponse) {
            return $file;
        }

        if ($file->is_parsed) {
            return redirect()
                ->route('file_rows.index', $file->id)
                ->with('error', 'This file has already been parsed.');
        }

        $validated = $request->validate([
            'header_rows' => ['required', 'array', 'min:1'],
            'header_rows.*' => ['required', 'integer', 'min:1', 'distinct'],
        ]);

        $headerRows = array_map('intval', $validated['header_rows']);
        $headerSet = array_fill_keys($headerRows, true);

        $disk = Storage::disk('local');

        if (! $disk->exists($file->filepath)) {
            return redirect()
                ->route('file_rows.index', $file->id)
                ->with('error', 'The CSV file could not be found.');
        }

        $path = $disk->path($file->filepath);
        $uuid = (string) Str::uuid();

        FileRowLog::create([
            'uuid' => $uuid,
            'file_upload_id' => $file->id,
            'status' => 'started',
        ]);

        try {
            DB::transaction(function () use ($file, $path, $headerSet): void {
                $handle = fopen($path, 'rb');

                if ($handle === false) {
                    throw new RuntimeException('The CSV file could not be opened.');
                }

                $rowNumber = 0;
                $seenHeaders = [];
                $chunk = [];
                $timestamp = now();

                try {
                    while (($cells = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                        $rowNumber++;

                        $data = [];

                        foreach ($cells as $index => $cell) {
                            $data[$index + 1] = $cell ?? '';
                        }

                        $json = json_encode($data);

                        if ($json === false) {
                            throw new RuntimeException(
                                'Row '.$rowNumber.' could not be encoded as JSON: '.json_last_error_msg()
                            );
                        }

                        $isHeader = isset($headerSet[$rowNumber]);

                        if ($isHeader) {
                            $seenHeaders[$rowNumber] = true;
                        }

                        $chunk[] = [
                            'file_upload_id' => $file->id,
                            'row_number' => $rowNumber,
                            'is_header' => $isHeader,
                            'data' => $json,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];

                        if (count($chunk) >= 500) {
                            FileRow::insert($chunk);
                            $chunk = [];
                        }
                    }
                } finally {
                    fclose($handle);
                }

                if ($chunk !== []) {
                    FileRow::insert($chunk);
                }

                $missing = array_diff(array_keys($headerSet), array_keys($seenHeaders));

                if ($missing !== []) {
                    sort($missing);

                    throw new RuntimeException(
                        'Header row(s) '.implode(', ', $missing).' do not exist in the file.'
                    );
                }

                $file->update([
                    'header_rows' => count($headerSet),
                    'is_parsed' => true,
                    'parsed_date' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            FileRowLog::create([
                'uuid' => $uuid,
                'file_upload_id' => $file->id,
                'status' => 'error',
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('file_rows.index', $file->id)
                ->with('error', 'There was an error processing the file.');
        }

        FileRowLog::create([
            'uuid' => $uuid,
            'file_upload_id' => $file->id,
            'status' => 'completed',
        ]);

        return redirect()
            ->route('file_rows.index', $file->id)
            ->with('status', 'File parsed successfully.');
    }

    /**
     * @return array{0: list<array{row_number: int, cells: list<string>}>, 1: int}|null
     */
    private function readCsvHead(string $filepath, int $limit): ?array
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($filepath)) {
            return null;
        }

        $handle = fopen($disk->path($filepath), 'rb');

        if ($handle === false) {
            return null;
        }

        $rows = [];
        $count = 0;

        try {
            while (($cells = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                $count++;

                if (count($rows) < $limit) {
                    $rows[] = [
                        'row_number' => $count,
                        'cells' => array_map(fn ($cell) => $cell ?? '', $cells),
                    ];
                }
            }
        } finally {
            fclose($handle);
        }

        return [$rows, $count];
    }

    private function findFile(string $fileUploadId): FileUpload|RedirectResponse
    {
        if (! is_numeric($fileUploadId)) {
            return $this->fileError('The file upload id must be numeric.');
        }

        if (! $this->isValidId($fileUploadId)) {
            return $this->fileError('The file upload id format must be valid.');
        }

        $file = FileUpload::find((int) $fileUploadId);

        if ($file === null) {
            return $this->fileError(
                "The file upload id {$fileUploadId} did not match an existing file upload."
            );
        }

        return $file;
    }

    private function fileError(string $message): RedirectResponse
    {
        return redirect()->route('files.index')->with('error', $message);
    }

    /**
     * Ids are plain positive integers that fit a signed 64-bit primary key.
     */
    private function isValidId(string $value): bool
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
