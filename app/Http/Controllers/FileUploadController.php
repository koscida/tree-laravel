<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('files.show', ['file' => $file]);
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
