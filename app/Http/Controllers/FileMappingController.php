<?php

namespace App\Http\Controllers;

use App\Models\DatePart;
use App\Models\FileMapping;
use App\Models\FileRow;
use App\Models\FileUpload;
use App\Models\Milestone;
use App\Models\NamePart;
use App\Models\RecordLabel;
use App\Models\Relationship;
use App\Models\SourcePart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class FileMappingController extends Controller
{
    /**
     * @var list<string>
     */
    private const MAP_KINDS = [
        'relationship',
        'milestone_date',
        'milestone_location',
        'record_label',
        'skip',
        'source',
    ];

    public function index(string $fileUploadId): View|RedirectResponse
    {
        $file = FileUpload::findValid($fileUploadId);

        if (is_string($file)) {
            return redirect()->route('files.index')->with('error', $file);
        }

        $headerRows = $file->fileRows()
            ->where('is_header', true)
            ->orderBy('row_number')
            ->get();

        if ($headerRows->isEmpty()) {
            return redirect()
                ->route('files.show', $file)
                ->with('error', 'This CSV has not been parsed yet.');
        }

        $fileRows = $file->fileRows()
            ->where('is_header', false)
            ->orderBy('row_number')
            ->paginate(25);

        return view('file_mappings.index', [
            'file' => $file,
            'headerRows' => $headerRows,
            'fileRows' => $fileRows,
            'fileMappings' => $file->fileMappings()->orderBy('column_idx')->get(),
            'relationships' => Relationship::orderBy('id')->get(),
            'nameParts' => NamePart::orderBy('id')->get(),
            'milestones' => Milestone::orderBy('id')->get(),
            'dateParts' => DatePart::orderBy('id')->get(),
            'sourceParts' => SourcePart::orderBy('id')->get(),
            'recordLabels' => RecordLabel::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request, string $fileUploadId): RedirectResponse
    {
        $file = FileUpload::findValid($fileUploadId);

        if (is_string($file)) {
            return redirect()->route('files.index')->with('error', $file);
        }

        if ($file->is_mapped) {
            return redirect()
                ->route('file_mappings.index', $file)
                ->with('error', 'This file has already been mapped.');
        }

        $headerRow = $this->headerRow($file);

        if ($headerRow === null) {
            return redirect()
                ->route('files.show', $file)
                ->with('error', 'This CSV has not been parsed yet.');
        }

        $columnIndexes = $this->columnIndexes($headerRow);

        $rules = [
            'is_mapped' => ['nullable', 'boolean'],
            'map_kind' => ['nullable', 'array'],
            'map_kind.*' => ['nullable', Rule::in(self::MAP_KINDS)],
        ];

        foreach ($columnIndexes as $columnIdx) {
            $kind = $request->input("map_kind.{$columnIdx}");

            if ($kind === 'relationship') {
                $rules["relationship_id.{$columnIdx}"] = ['required', 'integer', Rule::exists('relationships', 'id')];
                $rules["name_part_id.{$columnIdx}"] = ['required', 'integer', Rule::exists('name_parts', 'id')];
            } elseif ($kind === 'milestone_date') {
                $rules["milestone_id.{$columnIdx}"] = ['required', 'integer', Rule::exists('milestones', 'id')];
                $rules["date_part_id.{$columnIdx}"] = ['required', 'integer', Rule::exists('date_parts', 'id')];
            } elseif ($kind === 'milestone_location') {
                $rules["milestone_id.{$columnIdx}"] = ['required', 'integer', Rule::exists('milestones', 'id')];
            } elseif ($kind === 'record_label') {
                $rules["record_label_id.{$columnIdx}"] = ['required', 'integer', Rule::exists('record_labels', 'id')];
            } elseif ($kind === 'source') {
                $rules["source_part_id.{$columnIdx}"] = ['required', 'integer', Rule::exists('source_parts', 'id')];
            }
        }

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request, $columnIndexes): void {
            $expected = array_map(fn (int $columnIdx): string => (string) $columnIdx, $columnIndexes);
            $posted = array_map(
                fn (int|string $columnIdx): string => (string) $columnIdx,
                array_keys((array) $request->input('map_kind', [])),
            );

            if (array_diff($posted, $expected) !== []) {
                $validator->errors()->add('map_kind', 'The mapping contains an invalid column.');
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('file_mappings.index', $file)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $mapKinds = $validated['map_kind'] ?? [];
        $markComplete = (bool) ($validated['is_mapped'] ?? false);
        $allColumnsMapped = true;

        foreach ($columnIndexes as $columnIdx) {
            if (($mapKinds[$columnIdx] ?? '') === '') {
                $allColumnsMapped = false;
                break;
            }
        }

        try {
            DB::transaction(function () use ($file, $columnIndexes, $validated, $mapKinds, $markComplete, $allColumnsMapped): void {
                foreach ($columnIndexes as $columnIdx) {
                    $kind = $mapKinds[$columnIdx] ?? null;

                    if ($kind === null || $kind === '') {
                        continue;
                    }

                    $values = [
                        'map_kind' => $kind,
                        'relationship_id' => null,
                        'name_part_id' => null,
                        'milestone_id' => null,
                        'date_part_id' => null,
                        'record_label_id' => null,
                        'source_part_id' => null,
                    ];

                    if ($kind === 'relationship') {
                        $values['relationship_id'] = $validated['relationship_id'][$columnIdx];
                        $values['name_part_id'] = $validated['name_part_id'][$columnIdx];
                    } elseif ($kind === 'milestone_date') {
                        $values['milestone_id'] = $validated['milestone_id'][$columnIdx];
                        $values['date_part_id'] = $validated['date_part_id'][$columnIdx];
                    } elseif ($kind === 'milestone_location') {
                        $values['milestone_id'] = $validated['milestone_id'][$columnIdx];
                    } elseif ($kind === 'record_label') {
                        $values['record_label_id'] = $validated['record_label_id'][$columnIdx];
                    } elseif ($kind === 'source') {
                        $values['source_part_id'] = $validated['source_part_id'][$columnIdx];
                    }

                    FileMapping::updateOrCreate(
                        [
                            'file_upload_id' => $file->id,
                            'column_idx' => $columnIdx,
                        ],
                        $values,
                    );
                }

                if ($markComplete && $allColumnsMapped) {
                    $file->update([
                        'is_mapped' => true,
                        'mapped_date' => now(),
                    ]);
                }
            });
        } catch (Throwable $exception) {
            return redirect()
                ->route('file_mappings.index', $file)
                ->withErrors(['mapping' => 'The mappings could not be saved.'])
                ->withInput();
        }

        if ($markComplete && ! $allColumnsMapped) {
            return redirect()
                ->route('file_mappings.index', $file)
                ->withErrors(['map_kind' => 'Mapping is not complete, some column mappings are still blank.'])
                ->withInput();
        }

        return redirect()
            ->route('file_mappings.index', $file)
            ->with('status', 'Mappings saved successfully.');
    }

    private function headerRow(FileUpload $file): ?FileRow
    {
        return $file->fileRows()
            ->where('is_header', true)
            ->orderBy('row_number')
            ->first();
    }

    /**
     * @return list<int>
     */
    private function columnIndexes(FileRow $headerRow): array
    {
        $indexes = array_map('intval', array_keys($headerRow->data ?? []));
        sort($indexes);

        return $indexes;
    }
}
