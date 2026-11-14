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
}
