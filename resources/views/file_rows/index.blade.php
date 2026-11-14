@extends('layouts.app')

@section('title', 'File Rows')

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
                <li class="breadcrumb-item active" aria-current="page">File Rows</li>
            </ol>
        </nav>

        <h1 class="h3 mb-4">File Rows for {{ $file->filename }}</h1>

        @php
            $columnCount = collect($rows)->max(fn ($row) => count($row['cells'])) ?? 0;
        @endphp

        <form id="file-rows-form" method="POST" action="{{ route('file_rows.store', $file) }}">
            @csrf

            <p class="text-secondary">{{ count($rows) }}/{{ $totalRows }} lines shown</p>

            @if ($rows === [])
                <div class="alert alert-info mb-0" role="alert">
                    This CSV has no rows to display.
                </div>
            @else
                <div class="table-responsive border rounded" style="max-height: 60vh;">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th scope="col">Header row</th>
                                @for ($column = 1; $column <= $columnCount; $column++)
                                    <th scope="col" class="text-nowrap">Column {{ $column }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <th scope="row" class="text-nowrap">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input header-row-checkbox" type="checkbox"
                                                   name="header_rows[]"
                                                   id="header-row-{{ $row['row_number'] }}"
                                                   value="{{ $row['row_number'] }}">
                                            <label class="form-check-label" for="header-row-{{ $row['row_number'] }}">
                                                Row {{ $row['row_number'] }}
                                            </label>
                                        </div>
                                    </th>
                                    @for ($column = 0; $column < $columnCount; $column++)
                                        <td class="text-nowrap">{{ $row['cells'][$column] ?? '' }}</td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="d-flex align-items-center gap-3 mt-3">
                <button type="submit" class="btn btn-primary">Save header rows</button>
                <span id="header-rows-error" class="text-danger small" hidden>
                    Select at least one header row before saving.
                </span>
                @error('header_rows')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </form>

        <h2 class="h4 mt-5">Parse Attempts</h2>

        @if (empty($logsByUuid))
            <p class="text-secondary fst-italic mb-0">No parse attempts yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th scope="col">UUID</th>
                            <th scope="col">Started</th>
                            <th scope="col">Completed</th>
                            <th scope="col" colspan="2">Errors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logsByUuid as $uuid => $logs)
                            @php
                                $started = $logs->firstWhere('status', 'started');
                                $completed = $logs->firstWhere('status', 'completed');
                                $error = $logs->firstWhere('status', 'error');
                            @endphp
                            <tr>
                                <td><code>{{ $uuid }}</code></td>
                                <td>
                                    @if ($started)
                                        <span class="badge text-bg-secondary">{{ ucfirst($started->status) }}</span>
                                        {{ $started->created_at?->format('d M Y H:i') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($completed)
                                        <span class="badge text-bg-success">{{ ucfirst($completed->status) }}</span>
                                        {{ $completed->created_at?->format('d M Y H:i') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($error)
                                        {{ $error->message }}
                                    @endif
                                </td>
                                <td>
                                    @if ($error)
                                        {{ $error->created_at?->format('d M Y H:i') }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('file-rows-form');
            const error = document.getElementById('header-rows-error');

            if (!form || !error) {
                return;
            }

            form.addEventListener('submit', function (event) {
                if (form.querySelectorAll('input.header-row-checkbox:checked').length === 0) {
                    event.preventDefault();
                    error.hidden = false;
                }
            });
        })();
    </script>
@endpush
