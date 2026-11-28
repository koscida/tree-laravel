<?php

namespace App\Http\Controllers;

use App\Models\DatePart;
use App\Models\FileConnection;
use App\Models\FileMapping;
use App\Models\FileUpload;
use App\Models\Milestone;
use App\Models\NamePart;
use App\Models\RecordLabel;
use App\Models\Relationship;
use App\Models\SourcePart;
use App\Models\Surname;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class FileImportController extends Controller
{
    private const SURNAME_RELATIONSHIP_KEYS = ['child', 'father', 'mother'];

    private const SURNAME_NAME_PART_KEYS = ['last_name', 'last_name_standard'];

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

        $mappings = $this->surnameMappings($file);
        $matches = $this->surnameMatches($file, $mappings);
        $connectionCounts = $this->connectionCountsBySurname($file);

        $surnameCandidates = [];

        foreach ($matches as $spelling => $match) {
            $surnameCandidates[] = [
                'surname' => $spelling,
                'row_count' => count($match['row_numbers']),
                'connection_count' => $connectionCounts[$spelling] ?? 0,
            ];
        }

        $importedSurnames = Surname::query()
            ->join('file_connections', 'file_connections.surname_id', '=', 'surnames.id')
            ->join('file_rows', 'file_rows.id', '=', 'file_connections.file_row_id')
            ->where('file_rows.file_upload_id', $file->id)
            ->select('surnames.id', 'surnames.surname', 'surnames.canonical_surname_id')
            ->distinct()
            ->orderBy('surnames.surname')
            ->get();

        return view('file_imports.surnames', [
            'file' => $file,
            'surnameCandidates' => $surnameCandidates,
            'importedSurnames' => $importedSurnames,
        ]);
    }

    public function surnameStore(Request $request, string $fileUploadId): RedirectResponse
    {
        $file = FileUpload::findValid($fileUploadId);

        if (is_string($file)) {
            return redirect()->route('file_imports.index')->with('error', $file);
        }

        $validator = Validator::make($request->all(), [
            'action' => ['required', 'in:surname,alternative'],
            'surname' => ['required', 'string', 'max:255'],
            'canonical_id' => ['required_if:action,alternative', 'nullable', 'integer', 'exists:surnames,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('file_imports.surnames', $file)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $spelling = trim($validated['surname']);
        $matches = $this->surnameMatches($file, $this->surnameMappings($file));

        if (! isset($matches[$spelling])) {
            return redirect()->route('file_imports.surnames', $file)
                ->with('error', "The surname {$spelling} was not found in the file data.");
        }

        $canonical = null;

        if ($validated['action'] === 'alternative') {
            $canonical = Surname::find((int) $validated['canonical_id']);

            if ($canonical === null || $canonical->canonical_surname_id !== null) {
                return redirect()->route('file_imports.surnames', $file)
                    ->withErrors(['canonical_id' => 'The canonical surname id must match a canonical surname.']);
            }

            if ($canonical->surname === $spelling) {
                return redirect()->route('file_imports.surnames', $file)
                    ->withErrors(['canonical_id' => 'A surname cannot be an alternative spelling of itself.']);
            }
        }

        $surname = Surname::where('surname', $spelling)->first();

        if ($surname === null) {
            $surname = Surname::create([
                'surname' => $spelling,
                'canonical_surname_id' => $canonical?->id,
            ]);
        } elseif ($canonical !== null) {
            if ($surname->canonical_surname_id === null) {
                return redirect()->route('file_imports.surnames', $file)
                    ->withErrors(['canonical_id' => 'This surname is already an exact spelling and cannot be imported as an alternative.']);
            }

            if ($surname->canonical_surname_id !== $canonical->id) {
                return redirect()->route('file_imports.surnames', $file)
                    ->withErrors(['canonical_id' => 'This surname is already an alternative spelling of another surname.']);
            }
        }

        $newConnections = 0;

        DB::transaction(function () use ($matches, $spelling, $surname, &$newConnections): void {
            foreach ($matches[$spelling]['cells'] as $cell) {
                $connection = FileConnection::firstOrCreate([
                    'file_row_id' => $cell['file_row_id'],
                    'file_mapping_id' => $cell['file_mapping_id'],
                    'connection_kind' => 'surname',
                    'surname_id' => $surname->id,
                ], ['match_method' => 'manual']);

                if ($connection->wasRecentlyCreated) {
                    $newConnections++;
                }
            }
        });

        return redirect()->route('file_imports.surnames', $file)
            ->with('status', "Imported surname {$spelling} with {$newConnections} new connections.");
    }

    /**
     * @return Collection<int, FileMapping>
     */
    private function surnameMappings(FileUpload $file): Collection
    {
        $relationshipIds = Relationship::whereIn('key', self::SURNAME_RELATIONSHIP_KEYS)->pluck('id');
        $namePartIds = NamePart::whereIn('key', self::SURNAME_NAME_PART_KEYS)->pluck('id');

        return FileMapping::query()
            ->where('file_upload_id', $file->id)
            ->where('map_kind', 'relationship')
            ->whereIn('relationship_id', $relationshipIds)
            ->whereIn('name_part_id', $namePartIds)
            ->orderBy('column_idx')
            ->get();
    }

    /**
     * @param  Collection<int, FileMapping>  $mappings
     * @return array<string, array{row_numbers: array<int, int>, cells: list<array{file_row_id: int, file_mapping_id: int, row_number: int}>}>
     */
    private function surnameMatches(FileUpload $file, Collection $mappings): array
    {
        $matches = [];

        foreach ($file->fileRows()->where('is_header', false)->orderBy('row_number')->cursor() as $fileRow) {
            $data = $fileRow->data ?? [];

            foreach ($mappings as $mapping) {
                $value = $data[$mapping->column_idx] ?? $data[(string) $mapping->column_idx] ?? null;

                if ($value === null) {
                    continue;
                }

                $spelling = trim((string) $value);

                if ($spelling === '') {
                    continue;
                }

                $matches[$spelling]['row_numbers'][$fileRow->row_number] = $fileRow->row_number;
                $matches[$spelling]['cells'][] = [
                    'file_row_id' => $fileRow->id,
                    'file_mapping_id' => $mapping->id,
                    'row_number' => $fileRow->row_number,
                ];
            }
        }

        ksort($matches, SORT_NATURAL | SORT_FLAG_CASE);

        return $matches;
    }

    /**
     * @return array<string, int>
     */
    private function connectionCountsBySurname(FileUpload $file): array
    {
        return FileConnection::query()
            ->join('surnames', 'surnames.id', '=', 'file_connections.surname_id')
            ->join('file_rows', 'file_rows.id', '=', 'file_connections.file_row_id')
            ->where('file_rows.file_upload_id', $file->id)
            ->selectRaw('surnames.surname, COUNT(*) as connection_count')
            ->groupBy('surnames.surname')
            ->pluck('connection_count', 'surname')
            ->map(fn ($count): int => (int) $count)
            ->all();
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
