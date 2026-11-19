@extends('layouts.app')

@section('title', 'File Mappings')

@section('content')
    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('files.index') }}">Files</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('files.show', $file) }}">{{ $file->filename }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">File Mappings</li>
            </ol>
        </nav>

        <h1 class="h3 mb-4">Column Mapping for {{ $file->filename }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($file->is_mapped)
            <p class="mb-3">Date Mapped: <strong>{{ $file->mapped_date?->format('d-M-Y H:i') }}</strong></p>
        @endif

        @php
            $columnIndexes = array_keys($headerRows->first()->data ?? []);
            sort($columnIndexes);
            $mappingsByColumn = $fileMappings->keyBy('column_idx');
        @endphp

        <form method="POST" action="{{ route('file_mappings.store', $file) }}">
            @csrf

            <div class="table-responsive border rounded">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            @foreach ($columnIndexes as $columnIdx)
                                <th scope="col" class="text-nowrap">Column {{ $columnIdx }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach ($columnIndexes as $columnIdx)
                                @php
                                    $mapping = $mappingsByColumn->get($columnIdx);
                                    $oldKind = old("map_kind.{$columnIdx}");
                                    $selectedKind = $oldKind !== null && $oldKind !== '' ? $oldKind : ($mapping?->map_kind ?? '');
                                    $isRelationship = $selectedKind === 'relationship';
                                    $isMilestone = in_array($selectedKind, ['milestone_date', 'milestone_location'], true);
                                    $isMilestoneDate = $selectedKind === 'milestone_date';
                                    $isRecordLabel = $selectedKind === 'record_label';
                                    $isSource = $selectedKind === 'source';
                                    $hasSecond = in_array($selectedKind, ['relationship', 'milestone_date', 'milestone_location', 'record_label', 'source'], true);
                                    $hasThird = in_array($selectedKind, ['relationship', 'milestone_date'], true);
                                @endphp
                                <th scope="col">
                                    <div class="d-flex flex-column gap-1">
                                        <select name="map_kind[{{ $columnIdx }}]" class="form-select form-select-sm mapping-kind" data-column="{{ $columnIdx }}" @disabled($file->is_mapped)>
                                            @unless ($mapping)
                                                <option value="" @selected($selectedKind === '')>Choose a mapping</option>
                                            @endunless
                                            <option value="relationship" @selected($isRelationship)>Relationship</option>
                                            <option value="milestone_date" @selected($isMilestoneDate)>Milestone Date</option>
                                            <option value="milestone_location" @selected($selectedKind === 'milestone_location')>Milestone Location</option>
                                            <option value="record_label" @selected($isRecordLabel)>Record Label</option>
                                            <option value="source" @selected($isSource)>Source</option>
                                            <option value="skip" @selected($selectedKind === 'skip')>Skip</option>
                                        </select>

                                        <select name="source_part_id[{{ $columnIdx }}]" @class(['form-select', 'form-select-sm', 'mapping-field', 'd-none' => ! $isSource]) data-column="{{ $columnIdx }}" data-kinds="source" @disabled($file->is_mapped || ! $isSource)>
                                            <option value="">Choose a source part</option>
                                            @foreach ($sourceParts as $sourcePart)
                                                <option value="{{ $sourcePart->id }}" @selected((string) old("source_part_id.{$columnIdx}", $mapping?->source_part_id) === (string) $sourcePart->id)>{{ $sourcePart->label }}</option>
                                            @endforeach
                                        </select>

                                        <select name="relationship_id[{{ $columnIdx }}]" @class(['form-select', 'form-select-sm', 'mapping-field', 'd-none' => ! $isRelationship]) data-column="{{ $columnIdx }}" data-kinds="relationship" @disabled($file->is_mapped || ! $isRelationship)>
                                            <option value="">Choose a relationship</option>
                                            @foreach ($relationships as $relationship)
                                                <option value="{{ $relationship->id }}" @selected((string) old("relationship_id.{$columnIdx}", $mapping?->relationship_id) === (string) $relationship->id)>{{ $relationship->label }}</option>
                                            @endforeach
                                        </select>

                                        <select name="milestone_id[{{ $columnIdx }}]" @class(['form-select', 'form-select-sm', 'mapping-field', 'd-none' => ! $isMilestone]) data-column="{{ $columnIdx }}" data-kinds="milestone_date milestone_location" @disabled($file->is_mapped || ! $isMilestone)>
                                            <option value="">Choose a milestone</option>
                                            @foreach ($milestones as $milestone)
                                                <option value="{{ $milestone->id }}" @selected((string) old("milestone_id.{$columnIdx}", $mapping?->milestone_id) === (string) $milestone->id)>{{ $milestone->label }}</option>
                                            @endforeach
                                        </select>

                                        <select name="record_label_id[{{ $columnIdx }}]" @class(['form-select', 'form-select-sm', 'mapping-field', 'd-none' => ! $isRecordLabel]) data-column="{{ $columnIdx }}" data-kinds="record_label" @disabled($file->is_mapped || ! $isRecordLabel)>
                                            <option value="">Choose a record label</option>
                                            @foreach ($recordLabels as $recordLabel)
                                                <option value="{{ $recordLabel->id }}" @selected((string) old("record_label_id.{$columnIdx}", $mapping?->record_label_id) === (string) $recordLabel->id)>{{ $recordLabel->label }}</option>
                                            @endforeach
                                        </select>

                                        <select name="name_part_id[{{ $columnIdx }}]" @class(['form-select', 'form-select-sm', 'mapping-field', 'd-none' => ! $isRelationship]) data-column="{{ $columnIdx }}" data-kinds="relationship" @disabled($file->is_mapped || ! $isRelationship)>
                                            <option value="">Choose a name part</option>
                                            @foreach ($nameParts as $namePart)
                                                <option value="{{ $namePart->id }}" @selected((string) old("name_part_id.{$columnIdx}", $mapping?->name_part_id) === (string) $namePart->id)>{{ $namePart->label }}</option>
                                            @endforeach
                                        </select>

                                        <select name="date_part_id[{{ $columnIdx }}]" @class(['form-select', 'form-select-sm', 'mapping-field', 'd-none' => ! $isMilestoneDate]) data-column="{{ $columnIdx }}" data-kinds="milestone_date" @disabled($file->is_mapped || ! $isMilestoneDate)>
                                            <option value="">Choose a date part</option>
                                            @foreach ($dateParts as $datePart)
                                                <option value="{{ $datePart->id }}" @selected((string) old("date_part_id.{$columnIdx}", $mapping?->date_part_id) === (string) $datePart->id)>{{ $datePart->label }}</option>
                                            @endforeach
                                        </select>

                                        <select @class(['form-select', 'form-select-sm', 'mapping-placeholder', 'd-none' => $hasSecond]) data-column="{{ $columnIdx }}" data-slot="second" disabled>
                                            <option value="" selected>No second mapping</option>
                                        </select>

                                        <select @class(['form-select', 'form-select-sm', 'mapping-placeholder', 'd-none' => $hasThird]) data-column="{{ $columnIdx }}" data-slot="third" disabled>
                                            <option value="" selected>No third mapping</option>
                                        </select>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                        @foreach ($headerRows as $headerRow)
                            <tr>
                                @foreach ($columnIndexes as $columnIdx)
                                    <td class="text-nowrap">{{ $headerRow->data[$columnIdx] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </thead>
                    <tbody>
                        @forelse ($fileRows as $fileRow)
                            <tr>
                                @foreach ($columnIndexes as $columnIdx)
                                    <td class="text-nowrap">{{ $fileRow->data[$columnIdx] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columnIndexes) }}" class="text-secondary fst-italic">No data rows.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="is_mapped" id="is_mapped" value="1" @checked($file->is_mapped) @disabled($file->is_mapped)>
                <label class="form-check-label" for="is_mapped">Mapping Complete</label>
            </div>

            @if ($fileRows->hasPages())
                <div class="mt-3">
                    {{ $fileRows->links() }}
                </div>
            @endif

            <button type="submit" class="btn btn-primary mt-3" @disabled($file->is_mapped)>Submit</button>
        </form>
    </div>
@endsection

@push('scripts')
    @if (! $file->is_mapped)
        <script>
            document.querySelectorAll('.mapping-kind').forEach((kindSelect) => {
                const column = kindSelect.dataset.column;
                const fields = Array.from(document.querySelectorAll(`.mapping-field[data-column="${column}"]`));
                const secondPlaceholder = document.querySelector(`.mapping-placeholder[data-column="${column}"][data-slot="second"]`);
                const thirdPlaceholder = document.querySelector(`.mapping-placeholder[data-column="${column}"][data-slot="third"]`);

                const kindsFor = (field) => field.dataset.kinds.split(' ').filter(Boolean);

                const sync = () => {
                    const kind = kindSelect.value;

                    fields.forEach((field) => {
                        const relevant = kindsFor(field).includes(kind);
                        field.classList.toggle('d-none', !relevant);
                        field.disabled = !relevant;
                    });

                    if (secondPlaceholder) {
                        secondPlaceholder.classList.toggle('d-none', ['relationship', 'milestone_date', 'milestone_location', 'record_label', 'source'].includes(kind));
                    }

                    if (thirdPlaceholder) {
                        thirdPlaceholder.classList.toggle('d-none', ['relationship', 'milestone_date'].includes(kind));
                    }
                };

                kindSelect.addEventListener('change', () => {
                    const kind = kindSelect.value;

                    fields.forEach((field) => {
                        if (kindsFor(field).includes(kind)) {
                            field.selectedIndex = 0;
                        }
                    });

                    sync();
                });

                sync();
            });
        </script>
    @endif
@endpush
