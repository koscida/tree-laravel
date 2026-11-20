@extends('layouts.app')

@section('title', 'Import')

@section('content')
    @php
        $mappingsByColumn = $fileMappings->keyBy('column_idx');
        $mapKindColors = [
            'relationship' => '#fee',
            'milestone_date' => '#eef',
            'milestone_location' => '#eff',
            'record_label' => '#efe',
            'source' => '#ffe',
            'skip' => '#eee',
        ];
        $mapKindWidths = [
            'relationship' => '150px',
            'milestone_date' => '30px',
            'milestone_location' => '30px',
            'record_label' => '15px',
            'source' => '300px',
            'skip' => '5px',
        ];
    @endphp

    <div class="container py-5">
        <h1 class="h3 mb-4">{{ $file->filename }}</h1>

        <div class="row">

            <div class="col-md-8">
                <div class="row g-3 mb-3" id="file-import-filters">
                    @php
                        $filterDropdowns = [
                            [
                                'key'       => 'relationships',
                                'label'     => 'Relationships',
                                'data'      => $relationships,
                            ],
                            [
                                'key'       => 'name-parts',
                                'label'     => 'Name Parts',
                                'data'      => $nameParts,
                            ],
                            [
                                'key'       => 'milestones',
                                'label'     => 'Milestones',
                                'data'      => $milestones,
                            ],
                            [
                                'key'       => 'date-parts',
                                'label'     => 'Date Parts',
                                'data'      => $dateParts,
                            ],
                            [
                                'key'       => 'source-parts',
                                'label'     => 'Source Parts',
                                'data'      => $sourceParts,
                            ],
                            [
                                'key'       => 'record-labels',
                                'label'     => 'Record Labels',
                                'data'      => $recordLabels,
                            ],
                        ]
                    @endphp
                    @foreach ($filterDropdowns as $filterDropdown)
                        <div class="col">
                            <label class="form-label" for="filter-{{ $filterDropdown['key'] }}">{{ $filterDropdown['label'] }}</label>
                            <select id="filter-{{ $filterDropdown['key'] }}" class="form-select" multiple size="5"
                                data-filter-key="{{ $filterDropdown['key'] }}" onchange="treeFiltersChanged()">
                                @foreach ($filterDropdown['data'] as $d)
                                    <option value="{{ $d->id }}">{{ $d->label }}</option>
                                @endforeach
                            </select>
                            <div class="mt-1 d-flex flex-row justify-content-between align-items-center">
                                <button class="btn btn-tiny btn-outline-primary"
                                    aria-label="Select all {{ $filterDropdown['label'] }} in filter"
                                    aria-controls="filter-{{ $filterDropdown['key'] }}"
                                    onclick="selectAllTreeFilter('{{ $filterDropdown['key'] }}')">Select All</button>
                                <button class="btn btn-tiny btn-outline-danger"
                                    aria-label="Clear {{ $filterDropdown['label'] }} filter"
                                    aria-controls="filter-{{ $filterDropdown['key'] }}"
                                    onclick="clearTreeFilter('{{ $filterDropdown['key'] }}')">Clear</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="w-100" style="max-height: 500px; overflow: auto;">
                    <table class="table table-sm table-striped align-middle" id="file-import-table">
                        <thead style="max-height: 130px; position: sticky; top: 0; z-index: 3;">
                            <tr>
                                <th scope="col" class="bg-white" style="position: sticky; left: 0; z-index: 2;">CSV Row</th>
                                @foreach ($column_idxs as $column_idx)
                                    @php($mapping = $mappingsByColumn[$column_idx])
                                    <th scope="col" class="bg-white" style="position: relative; z-index: 1;"
                                        data-column-idx="{{ $column_idx }}"
                                        data-map-kind="{{ $mapping->map_kind }}"
                                        data-relationship-id="{{ $mapping->relationship_id }}"
                                        data-name-part-id="{{ $mapping->name_part_id }}"
                                        data-milestone-id="{{ $mapping->milestone_id }}"
                                        data-date-part-id="{{ $mapping->date_part_id }}"
                                        data-source-part-id="{{ $mapping->source_part_id }}"
                                        data-record-label-id="{{ $mapping->record_label_id }}">Column {{ $column_idx }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th scope="col" class="bg-white" style="position: sticky; left: 0; z-index: 2;"></th>
                                @foreach ($column_idxs as $column_idx)
                                    @php($mapping = $mappingsByColumn[$column_idx])
                                    <th scope="col" class="bg-white" style="position: relative; z-index: 1;"
                                        data-column-idx="{{ $column_idx }}"
                                        data-map-kind="{{ $mapping->map_kind }}"
                                        data-relationship-id="{{ $mapping->relationship_id }}"
                                        data-name-part-id="{{ $mapping->name_part_id }}"
                                        data-milestone-id="{{ $mapping->milestone_id }}"
                                        data-date-part-id="{{ $mapping->date_part_id }}"
                                        data-source-part-id="{{ $mapping->source_part_id }}"
                                        data-record-label-id="{{ $mapping->record_label_id }}">
                                        {{ $mapping->map_kind }}
                                        <br/>
                                        {!! implode('<br/>', array_filter([$mapping->relationship_id ? e($relationships->firstWhere('id', $mapping->relationship_id)?->key) : null, $mapping->name_part_id ? e($nameParts->firstWhere('id', $mapping->name_part_id)?->key) : null, $mapping->milestone_id ? e($milestones->firstWhere('id', $mapping->milestone_id)?->key) : null, $mapping->date_part_id ? e($dateParts->firstWhere('id', $mapping->date_part_id)?->key) : null, $mapping->source_part_id ? e($sourceParts->firstWhere('id', $mapping->source_part_id)?->key) : null, $mapping->record_label_id ? e($recordLabels->firstWhere('id', $mapping->record_label_id)?->key) : null])) !!}
                                    </th>
                                @endforeach
                            </tr>
                            @foreach ($headerRows as $headerRow)
                                <tr stlye="padding-left:150px;">
                                    <th scope="col" class="bg-white" style="position: sticky; left: 0; z-index: 2;">{{ $headerRow->row_number }}</th>
                                    @foreach ($column_idxs as $column_idx)
                                        @php($mapping = $mappingsByColumn[$column_idx])
                                        @if (isset($headerRow->data[$column_idx]))
                                            <th scope="col" class="bg-white" style="position: relative; z-index: 1;"
                                                data-column-idx="{{ $column_idx }}"
                                                data-map-kind="{{ $mapping->map_kind }}"
                                                data-relationship-id="{{ $mapping->relationship_id }}"
                                                data-name-part-id="{{ $mapping->name_part_id }}"
                                                data-milestone-id="{{ $mapping->milestone_id }}"
                                                data-date-part-id="{{ $mapping->date_part_id }}"
                                                data-source-part-id="{{ $mapping->source_part_id }}"
                                                data-record-label-id="{{ $mapping->record_label_id }}">{{ $headerRow->data[$column_idx] }}</th>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </thead>
                        <tbody class="table-group-divider">
                            @foreach ($fileRows as $fileRow)
                                <tr>
                                    <th scope="row" style="position: sticky; left: 0; z-index: 1;">{{ $fileRow->row_number }}</th>
                                    @foreach ($column_idxs as $column_idx)
                                        @php($mapping = $mappingsByColumn[$column_idx])
                                        @if (isset($fileRow->data[$column_idx]))
                                            <td data-column-idx="{{ $column_idx }}"
                                                data-map-kind="{{ $mapping->map_kind }}"
                                                data-relationship-id="{{ $mapping->relationship_id }}"
                                                data-name-part-id="{{ $mapping->name_part_id }}"
                                                data-milestone-id="{{ $mapping->milestone_id }}"
                                                data-date-part-id="{{ $mapping->date_part_id }}"
                                                data-source-part-id="{{ $mapping->source_part_id }}"
                                                data-record-label-id="{{ $mapping->record_label_id }}"
                                                style="background-color: {{ $mapKindColors[$mapping->map_kind] }}; width: {{ $mapKindWidths[$mapping->map_kind] }};">{{ \Illuminate\Support\Str::limit($fileRow->data[$column_idx], 60) }}</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $fileRows->links() }}
                </div>
            </div>

            <div class="col-md-4">

            </div>

        </div>


    </div>

    <script>
        const treeFilterCookieName = 'tree-filters-file-imports-{{ $file->id }}';
        const treeFilterSelects = Array.from(document.querySelectorAll('#file-import-filters select[data-filter-key]'));
        const treeFilterAttributes = {
            relationships: 'data-relationship-id',
            name_parts: 'data-name-part-id',
            milestones: 'data-milestone-id',
            date_parts: 'data-date-part-id',
            source_parts: 'data-source-part-id',
            record_labels: 'data-record-label-id',
        };

        function treeFilterSelectedValues(select) {
            return Array.from(select.selectedOptions).map(function (option) {
                return option.value;
            });
        }

        function treeFilterReadCookie() {
            const prefix = treeFilterCookieName + '=';
            const entry = document.cookie.split(';').map(function (part) {
                return part.trim();
            }).find(function (part) {
                return part.startsWith(prefix);
            });

            if (!entry) {
                return {};
            }

            try {
                return JSON.parse(decodeURIComponent(entry.slice(prefix.length)));
            } catch (error) {
                return {};
            }
        }

        function treeFilterApply() {
            const selections = {};

            treeFilterSelects.forEach(function (select) {
                const values = treeFilterSelectedValues(select);

                if (values.length > 0) {
                    selections[select.dataset.filterKey] = values;
                }
            });

            const activeKeys = Object.keys(selections);

            document.querySelectorAll('#file-import-table [data-column-idx]').forEach(function (cell) {
                const visible = activeKeys.every(function (key) {
                    const value = cell.getAttribute(treeFilterAttributes[key]) || '';

                    return selections[key].includes(value);
                });

                cell.style.display = visible ? '' : 'none';
            });
        }

        function treeFilterSave() {
            const filters = {};

            treeFilterSelects.forEach(function (select) {
                filters[select.dataset.filterKey] = treeFilterSelectedValues(select);
            });

            const cookieExpiration = 30 * 24 * 60 * 60;
            document.cookie = treeFilterCookieName + '=' + encodeURIComponent(JSON.stringify(filters)) + '; path=/; max-age=' + cookieExpiration;
        }

        function treeFiltersChanged() {
            treeFilterSave();
            treeFilterApply();
        }

        function clearTreeFilter(filterId) {
            changeAllTreeFilter(filterId, false);
        }

        function selectAllTreeFilter(filterId) {
            changeAllTreeFilter(filterId, true);
        }

        function changeAllTreeFilter(filterId, val) {
            const select = document.getElementById('filter-' + filterId);

            if (!select) {
                return;
            }

            Array.from(select.options).forEach(function (option) {
                option.selected = val;
            });

            treeFiltersChanged();
        }

        const savedTreeFilters = treeFilterReadCookie();

        treeFilterSelects.forEach(function (select) {
            const values = savedTreeFilters[select.dataset.filterKey] || [];

            Array.from(select.options).forEach(function (option) {
                option.selected = values.includes(option.value);
            });
        });

        treeFilterApply();
    </script>
@endsection
