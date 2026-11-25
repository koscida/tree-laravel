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

        $filterDropdowns = [
            'relationships' => [
                'key'       => 'relationships',
                'label'     => 'Relationships',
                'data'      => $relationships,
            ],
            'name-parts' => [
                'key'       => 'name-parts',
                'label'     => 'Name Parts',
                'data'      => $nameParts,
            ],
            'milestones' => [
                'key'       => 'milestones',
                'label'     => 'Milestones',
                'data'      => $milestones,
            ],
            'date-parts' => [
                'key'       => 'date-parts',
                'label'     => 'Date Parts',
                'data'      => $dateParts,
            ],
            'source-parts' => [
                'key'       => 'source-parts',
                'label'     => 'Source Parts',
                'data'      => $sourceParts,
            ],
            'record-labels' => [
                'key'       => 'record-labels',
                'label'     => 'Record Labels',
                'data'      => $recordLabels,
            ],
        ];

        $linkColumns = [
            [
                'label' => 'Child',
                'map_kind' => 'relationship',
                'relationship_id' => '1',
            ],
            [
                'label' => 'Father',
                'map_kind' => 'relationship',
                'relationship_id' => '6',
            ],
            [
                'label' => 'Mother',
                'map_kind' => 'relationship',
                'relationship_id' => '17',
            ],
        ];

        function standardizeMapping($mapping) {
            return [
                'map_kind' => is_object($mapping)
                    ? ($mapping->map_kind ?? '')
                    : ($mapping['map_kind'] ?? ''),
                'relationship_id' => is_object($mapping)
                    ? ($mapping->relationship_id ?? '')
                    : ($mapping['relationship_id'] ?? ''),
                'name_part_id' => is_object($mapping)
                    ? ($mapping->name_part_id ?? '')
                    : ($mapping['name_part_id'] ?? ''),
                'milestone_id' => is_object($mapping)
                    ? ($mapping->milestone_id ?? '')
                    : ($mapping['milestone_id'] ?? ''),
                'date_part_id' => is_object($mapping)
                    ? ($mapping->date_part_id ?? '')
                    : ($mapping['date_part_id'] ?? ''),
                'source_part_id' => is_object($mapping)
                    ? ($mapping->source_part_id ?? '')
                    : ($mapping['source_part_id'] ?? ''),
                'record_label_id' => is_object($mapping)
                    ? ($mapping->record_label_id ?? '')
                    : ($mapping['record_label_id'] ?? ''),
            ];
        }

        $displayMappingKeys = function ($mapping) use (
            $relationships,
            $nameParts,
            $milestones,
            $dateParts,
            $sourceParts,
            $recordLabels
        ) {
            $mapping = standardizeMapping($mapping);
            return implode(
                '<br/>',
                array_filter([
                    $mapping['relationship_id']
                        ? e($relationships->firstWhere('id', $mapping['relationship_id'])?->key)
                        : null,
                    $mapping['name_part_id']
                        ? e($nameParts->firstWhere('id', $mapping['name_part_id'])?->key)
                        : null,
                    $mapping['milestone_id']
                        ? e($milestones->firstWhere('id', $mapping['milestone_id'])?->key)
                        : null,
                    $mapping['date_part_id']
                        ? e($dateParts->firstWhere('id', $mapping['date_part_id'])?->key)
                        : null,
                    $mapping['source_part_id']
                        ? e($sourceParts->firstWhere('id', $mapping['source_part_id'])?->key)
                        : null,
                    $mapping['record_label_id']
                        ? e($recordLabels->firstWhere('id', $mapping['record_label_id'])?->key)
                        : null
                ])
            );
        };

        $displayCellData = function ($idx, $mapping) {
            $mapping = standardizeMapping($mapping);

            return 'data-column-idx="' . ($idx) . '"' .
                'data-map-kind="' . ($mapping['map_kind'] ?? null) . '"' .
                'data-relationship-id="' . ($mapping['relationship_id'] ?? null) . '"' .
                'data-name-part-id="' . ($mapping['name_part_id'] ?? null) . '"' .
                'data-milestone-id="' . ($mapping['milestone_id'] ?? null) . '"' .
                'data-date-part-id="' . ($mapping['date_part_id'] ?? null) . '"' .
                'data-source-part-id="' . ($mapping['source_part_id'] ?? null) . '"' .
                'data-record-label-id="' . ($mapping['record_label_id'] ?? null) . '"';
        };
    @endphp

    <div class="container py-5">
        <h1 class="h3 mb-4">{{ $file->filename }}</h1>

        <div class="row g-3 mb-3" id="file-import-filters">
            @foreach ($filterDropdowns as $filterDropdown)
                <div class="col-3">
                    <div class="d-flex flex-row justify-content-start align-items-center gap-2">
                        <label class="form-label m-0 p-0" for="filter-{{ $filterDropdown['key'] }}">{{ $filterDropdown['label'] }}</label>
                    </div>
                    <div class="card p-1">
                        <div class="mb-1 d-flex flex-row justify-content-between align-items-center">
                            <small class="text-sm fw-light fst-italic lh-1" id="filter-{{ $filterDropdown['key'] }}-count"></small>

                            <div class="d-flex flex-row justify-content-between align-items-center gap-1">
                                <button class="btn btn-tiny btn-outline-primary"
                                    aria-label="Select all {{ $filterDropdown['label'] }} in filter"
                                    aria-controls="filter-{{ $filterDropdown['key'] }}"
                                    onclick="handleTreeFilterSelectAll('{{ $filterDropdown['key'] }}')">Select All</button>
                                <button class="btn btn-tiny btn-outline-danger"
                                    aria-label="Clear {{ $filterDropdown['label'] }} filter"
                                    aria-controls="filter-{{ $filterDropdown['key'] }}"
                                    onclick="handleTreeFilterClear('{{ $filterDropdown['key'] }}')">Clear</button>
                            </div>
                        </div>

                        <select id="filter-{{ $filterDropdown['key'] }}" class="form-select" size="5" multiple aria-label="Multiple select example"
                            data-filter-key="{{ $filterDropdown['key'] }}" onchange="handleTreeFiltersChange()">
                            @foreach ($filterDropdown['data'] as $d)
                                <option value="{{ $d->id }}">{{ $d->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="w-100 d-flex gap-2">
            <div id="file-import-scroll-left" class="flex-grow-1" style="height: 500px; min-width: 0; overflow: auto;">
                <table class="table table-sm table-striped align-middle file-import-table" id="file-import-table">
                    <thead style="position: sticky; top: 0; z-index: 3;">
                        <tr>
                            <th scope="col" class="bg-white" style="position: sticky; left: 0; z-index: 2;">
                                <div style="height: 80px; width: 80px; overflow: none;">
                                    <span>CSV Row</span>
                                    <p style="font-weight: normal; font-size: 0.8rem; line-height: 1.15rem">
                                        Map Kind<br/>
                                        Mapping 1<br/>
                                        Mapping 2
                                    </p>
                                </div>
                            </th>
                            @foreach ($column_idxs as $column_idx)
                                @php($mapping = $mappingsByColumn[$column_idx])
                                <th scope="col" class="bg-white" style="position: relative; z-index: 1;"
                                    {!! $displayCellData($column_idx, $mapping) !!} >
                                    <div style="height: 80px; overflow: none;">
                                        <span>Column {{ $column_idx }}</span>
                                        <br/>
                                        <p style="font-weight: normal; font-size: 0.8rem; line-height: 1.15rem">
                                            {{ $mapping->map_kind }}
                                            <br/>
                                            {!! $displayMappingKeys($mapping) !!}
                                        </p>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                        @foreach ($headerRows as $headerRow)
                            <tr stlye="padding-left:150px;">
                                <th scope="col" class="bg-white" style="position: sticky; left: 0; z-index: 2;">
                                    <div style="height: 30px; overflow: scroll;">{{ $headerRow->row_number }}</div>
                                </th>
                                @foreach ($column_idxs as $column_idx)
                                    @php($mapping = $mappingsByColumn[$column_idx])
                                    @if (isset($headerRow->data[$column_idx]))
                                        <th scope="col" class="bg-white" style="position: relative; z-index: 1;"
                                            {!! $displayCellData($column_idx, $mapping) !!} >
                                            <div style="height: 30px; overflow: scroll;">{{ $headerRow->data[$column_idx] }}</div>
                                        </th>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </thead>

                    <tbody class="table-group-divider">
                        @foreach ($fileRows as $fileRow)
                            <tr>
                                <th scope="row" style="position: sticky; left: 0; z-index: 1;">
                                    <div style="height: 30px; overflow: scroll;">{{ $fileRow->row_number }}</div>
                                </th>
                                @foreach ($column_idxs as $column_idx)
                                    @php($mapping = $mappingsByColumn[$column_idx])
                                    @if (isset($fileRow->data[$column_idx]))
                                        <td {!! $displayCellData($column_idx, $mapping) !!}
                                            style="background-color: {{ $mapKindColors[$mapping->map_kind] }}; width: {{ $mapKindWidths[$mapping->map_kind] }};">
                                            <div style="height: 30px; overflow: scroll;">{{ \Illuminate\Support\Str::limit($fileRow->data[$column_idx], 60) }}</div>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div id="file-import-scroll-right" style="height: 500px; width: 200px; flex: 0 0 200px; overflow: auto;">
                <table class="table table-sm table-striped align-middle file-import-table">
                    <thead style="position: sticky; top: 0; z-index: 3;">
                        <tr>
                            @foreach ($linkColumns as $i => $linkColumn)
                                <th scope="col" class="bg-white" style="position: relative; z-index: 1;"
                                    {!! $displayCellData('100'.$i, $linkColumn) !!} >
                                    <div style="height: 80px; overflow: none;">
                                        <span>{{ $linkColumn['label'] }}</span>
                                        <br/>
                                        <p style="font-weight: normal; font-size: 0.8rem; line-height: 1.15rem">
                                            {{ $linkColumn['map_kind'] ?? '' }}
                                            <br/>
                                            {!! $displayMappingKeys($linkColumn) !!}
                                        </p>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                        @foreach ($headerRows as $i => $headerRow)
                            <tr>
                                @foreach ($linkColumns as $linkColumn)
                                    <th scope="col" class="bg-white" style="position: relative; z-index: 1;"
                                        {!! $displayCellData("100".$i, $linkColumn) !!} >
                                        <div style="height: 30px; overflow: scroll;"></div>
                                    </th>
                                @endforeach
                            </tr>
                        @endforeach
                    </thead>

                    <tbody class="table-group-divider">
                        @foreach ($fileRows as $fileRow)
                            <tr>
                                @foreach ($linkColumns as $i => $linkColumn)
                                    <td {!! $displayCellData("100".$i, $linkColumn) !!} >
                                        <div style="height: 30px; overflow: scroll;"
                                            >
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $fileRows->links() }}
        </div>

    </div>

    <script>
        const treeFilterCookieName = 'tree-filters-file-imports-{{ $file->id }}';
        const treeFilterSelects = Array.from(document.querySelectorAll('#file-import-filters select[data-filter-key]'));
        const treeFilterAttributes = {
            'relationships': 'data-relationship-id',
            'name-parts': 'data-name-part-id',
            'milestones': 'data-milestone-id',
            'date-parts': 'data-date-part-id',
            'source-parts': 'data-source-part-id',
            'record-labels': 'data-record-label-id',
        };

        function _treeFiltersSelectedValues() {
            const selections = {};

            treeFilterSelects.forEach(function (select) {
                const values = _treeFilterSelectedValues(select);

                if (values.length > 0) {
                    selections[select.dataset.filterKey] = values;
                }
            });

            return selections;
        }
        function _treeFilterSelectedValues(select) {
            return Array.from(select.selectedOptions).map(function (option) {
                return option.value;
            });
        }

        function _treeFilterReadCookie() {
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

        function _treeFilterApply() {
            const selections = _treeFiltersSelectedValues()

            const activeKeys = Object.keys(selections);
            const anyActive = activeKeys.length > 0

            // Show/Hide columns
            document.querySelectorAll('.file-import-table [data-column-idx]').forEach(function (cell) {
                    const visible = activeKeys.every(function (key) {
                    const value = cell.getAttribute(treeFilterAttributes[key]) || '';

                    return selections[key].includes(value);
                });

                cell.style.display = visible ? '' : 'none';
            });

            // Update filter displays
            treeFilterSelects.forEach(function (select) {
                const filterKey = select.dataset.filterKey;
                const countEle = document.getElementById(`filter-${filterKey}-count`);

                if(countEle) {
                    const selectedCount = `${((selections[filterKey] || []).length) || 0} selected`;

                    countEle.innerHTML = `${selectedCount}`;
                }
            })
        }

        function _treeFilterSave() {
            const selections = _treeFiltersSelectedValues()

            const cookieExpiration = 30 * 24 * 60 * 60;
            document.cookie = treeFilterCookieName + '=' + encodeURIComponent(JSON.stringify(selections)) + '; path=/; max-age=' + cookieExpiration;
        }

        function handleTreeFiltersChange() {
            _treeFilterSave();
            _treeFilterApply();
        }

        function handleTreeFilterClear(filterId) {
            _treeFilterChangeAll(filterId, false);
        }

        function handleTreeFilterSelectAll(filterId) {
            _treeFilterChangeAll(filterId, true);
        }

        function _treeFilterChangeAll(filterId, val) {
            const select = document.getElementById('filter-' + filterId);

            if (!select) {
                return;
            }

            Array.from(select.options).forEach(function (option) {
                option.selected = val;
            });

            handleTreeFiltersChange();
        }

        const savedTreeFilters = _treeFilterReadCookie();

        treeFilterSelects.forEach(function (select) {
            const values = savedTreeFilters[select.dataset.filterKey] || [];

            Array.from(select.options).forEach(function (option) {
                option.selected = values.includes(option.value);
            });
        });

        _treeFilterApply();

        const treeScrollLeft = document.getElementById('file-import-scroll-left');
        const treeScrollRight = document.getElementById('file-import-scroll-right');
        let treeScrollSyncing = false;
        let treeScrollSyncFrame = null;

        function syncTreeScroll(source, target) {
            if (!source || !target || treeScrollSyncing) {
                return;
            }

            treeScrollSyncing = true;
            target.scrollTop = source.scrollTop;

            if (treeScrollSyncFrame !== null) {
                cancelAnimationFrame(treeScrollSyncFrame);
            }

            treeScrollSyncFrame = requestAnimationFrame(function () {
                treeScrollSyncing = false;
                treeScrollSyncFrame = null;
            });
        }

        if (treeScrollLeft && treeScrollRight) {
            treeScrollRight.scrollTop = treeScrollLeft.scrollTop;

            treeScrollLeft.addEventListener('scroll', function () {
                syncTreeScroll(treeScrollLeft, treeScrollRight);
            });

            treeScrollRight.addEventListener('scroll', function () {
                syncTreeScroll(treeScrollRight, treeScrollLeft);
            });
        }
    </script>

@endsection
