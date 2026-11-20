<?php

namespace App\Http\Controllers;

use App\Models\DatePart;
use App\Models\FileUpload;
use App\Models\Milestone;
use App\Models\NamePart;
use App\Models\RecordLabel;
use App\Models\Relationship;
use App\Models\SourcePart;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FileImportController extends Controller
{
    public function index(): View
    {
        $fileUploads = FileUpload::where('is_parsed', true)
            ->where('is_mapped', true)
            ->orderByDesc('created_at')
            ->get();

        return view('file_imports.index', ['fileUploads' => $fileUploads]);
    }

    public function show(string $fileUploadId): View|RedirectResponse
    {
        $file = FileUpload::findValid($fileUploadId);

        if (is_string($file)) {
            return redirect()->route('file_imports.index')->with('error', $file);
        }

        $headerRows = $file->fileRows()
            ->where('is_header', true)
            ->orderBy('row_number')
            ->get();

        $fileRows = $file->fileRows()
            ->where('is_header', false)
            ->orderBy('row_number')
            ->paginate(25);

        $fileMappings = $file->fileMappings()
            ->orderBy('column_idx')
            ->get();

        $columnIdxs = $headerRows
            ->flatMap(fn ($headerRow) => array_keys($headerRow->data ?? []))
            ->unique()
            ->intersect($fileMappings->pluck('column_idx'))
            ->values()
            ->all();

        return view('file_imports.show', [
            'file' => $file,
            'headerRows' => $headerRows,
            'fileRows' => $fileRows,
            'fileMappings' => $fileMappings,
            'column_idxs' => $columnIdxs,
            'relationships' => Relationship::orderBy('id')->get(),
            'nameParts' => NamePart::orderBy('id')->get(),
            'milestones' => Milestone::orderBy('id')->get(),
            'dateParts' => DatePart::orderBy('id')->get(),
            'sourceParts' => SourcePart::orderBy('id')->get(),
            'recordLabels' => RecordLabel::orderBy('id')->get(),
        ]);
    }
}
