<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FileUploadController extends Controller
{
    public function index(): View
    {
        $files = FileUpload::orderByDesc('created_at')->get();

        return view('files.index', ['files' => $files]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'extensions:csv', 'max:51200'],
            'record_type' => ['required', 'in:birth,marriage,death'],
        ]);

        $file = $request->file('file');

        FileUpload::create([
            'filename' => $file->getClientOriginalName(),
            'filepath' => $file->storeAs('uploads', Str::uuid().'.csv', 'local'),
            'record_type' => $validated['record_type'],
        ]);

        return redirect()
            ->route('files.index')
            ->with('status', 'File uploaded successfully.');
    }

    public function show(string $fileUploadId): View|RedirectResponse
    {
        $file = FileUpload::findValid($fileUploadId);

        if (is_string($file)) {
            return redirect()->route('files.index')->with('error', $file);
        }

        $totalCsvRows = $this->countCsvRows($file->filepath);

        if ($totalCsvRows === null) {
            return redirect()
                ->route('files.index')
                ->with('error', 'The CSV file could not be found.');
        }

        $logs = $file->fileRowLogs()->orderBy('created_at')->orderBy('id')->get();

        return view('files.show', [
            'file' => $file,
            'parseAttemptCount' => $logs->pluck('uuid')->unique()->count(),
            'lastAttempt' => $logs->last(),
            'totalCsvRows' => $totalCsvRows,
            'totalFileRows' => $file->fileRows()->count(),
            'fileRowHeaderCount' => $file->fileRows()->where('is_header', true)->count(),
            'fileMappings' => $file->fileMappings()->orderBy('column_idx')->get(),
        ]);
    }

    private function countCsvRows(string $filepath): ?int
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($filepath)) {
            return null;
        }

        $handle = fopen($disk->path($filepath), 'rb');

        if ($handle === false) {
            return null;
        }

        $count = 0;

        try {
            while (fgetcsv($handle, 0, ',', '"', '') !== false) {
                $count++;
            }
        } finally {
            fclose($handle);
        }

        return $count;
    }
}
