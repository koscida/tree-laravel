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
use Illuminate\Http\Request;
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

    public function show(Request $request, string $fileUploadId): View|RedirectResponse
    {
        $file = FileUpload::findValid($fileUploadId);

        if (is_string($file)) {
            return redirect()->route('file_imports.index')->with('error', $file);
        }

        $headerRows = $file->fileRows()
            ->where('is_header', true)
            ->orderBy('row_number')
            ->get();

        $fileMappings = $file->fileMappings()
            ->orderBy('column_idx')
            ->get();
        $mappingsByColumnIdx = $fileMappings->keyBy('column_idx');

        $columnIdxs = $mappingsByColumnIdx->keys();

        $sortableColumns = $columnIdxs
            ->map(fn ($columnIdx) => (string) $columnIdx)
            ->prepend('row_number')
            ->all();

        $requestedSort = $request->query('sort', 'row_number');
        $sortBy = is_string($requestedSort) && in_array($requestedSort, $sortableColumns, true)
            ? $requestedSort
            : 'row_number';

        $requestedDirection = $request->query('direction', 'asc');
        $sortDirection = is_string($requestedDirection) && strtolower($requestedDirection) === 'desc'
            ? 'desc'
            : 'asc';

        $fileRowsQuery = $file->fileRows()
            ->where('is_header', false);

        if ($sortBy === 'row_number') {
            $fileRowsQuery->orderBy('row_number', $sortDirection);
        } else {
            $fileRowsQuery->orderByRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(data, ?)) '.$sortDirection,
                ['$."'.$sortBy.'"']
            )->orderBy('row_number');
        }

        $fileRows = $fileRowsQuery->paginate(25)->withQueryString();
        $fileRowsByRowNumber = $fileRows->keyBy('row_number');

        $rowNumbers = $fileRowsByRowNumber->keys();

        return view('file_imports.show', [
            'file' => $file,
            'headerRows' => $headerRows,
            'rowNumbers' => $rowNumbers,
            'fileRows' => $fileRows,
            'fileRowsByRowNumber' => $fileRowsByRowNumber,
            'columnIdxs' => $columnIdxs,
            'mappingsByColumnIdx' => $mappingsByColumnIdx,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
            'relationships' => Relationship::orderBy('id')->get(),
            'nameParts' => NamePart::orderBy('id')->get(),
            'milestones' => Milestone::orderBy('id')->get(),
            'dateParts' => DatePart::orderBy('id')->get(),
            'sourceParts' => SourcePart::orderBy('id')->get(),
            'recordLabels' => RecordLabel::orderBy('id')->get(),
        ]);
    }

    public function surnames(Request $request, string $fileUploadId): View|RedirectResponse
    {
        $file = FileUpload::findValid($fileUploadId);

        if (is_string($file)) {
            return redirect()->route('file_imports.index')->with('error', $file);
        }

        return view('file_imports.surnames', [
            'file' => $file,
        ]);
    }

    public function families(Request $request, string $fileUploadId): View|RedirectResponse
    {
        $file = FileUpload::findValid($fileUploadId);

        if (is_string($file)) {
            return redirect()->route('file_imports.index')->with('error', $file);
        }

        return view('file_imports.families', [
            'file' => $file,
        ]);
    }
}
