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

        <h1 class="h3 mb-4">CSV Preview for {{ $file->filename }}</h1>

        @php
            $columnCount = collect($rows)->max(fn ($row) => count($row['cells'])) ?? 0;
        @endphp

        <form id="file-rows-form" method="POST" action="{{ route('file_rows.store', $file) }}">
            @csrf

            <p class="text-secondary">{{ count($rows) }}/{{ $totalCsvRows }} lines shown</p>

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
                                                   value="{{ $row['row_number'] }}"
                                                   @disabled($file->is_parsed)>
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
                <button type="submit" class="btn btn-primary" @disabled($file->is_parsed)>Submit</button>
                <span id="header-rows-error" class="text-danger small" hidden>
                    Select at least one header row before saving.
                </span>
                @error('header_rows')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
        </form>

        <div class="mt-5">
            <h2 class="h4">File Rows</h2>

            @if (! $file->is_parsed)
                <p class="text-secondary fst-italic">This CSV has not been parsed yet.</p>
            @else
                <p class="mb-1">Stored Header Row Count: <strong>{{ $file->header_rows }}</strong></p>

                <p class="mb-1 mt-3">CSV Data:</p>
                <ul class="mb-0">
                    <li>Rows in file: <strong>{{ $totalCsvRows }}</strong></li>
                </ul>

                <p class="mb-1 mt-3">Parsed data:</p>
                <ul class="mb-3">
                    <li>Marked Header Rows: <strong>{{ $fileRowHeaderCount }}</strong></li>
                    <li>(Computed) Data Rows: <strong>{{ $totalFileRows - $fileRowHeaderCount }}</strong></li>
                    <li>Stored Rows: <strong>{{ $totalFileRows }}</strong></li>
                </ul>

                <div class="table-responsive border rounded">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Row</th>
                                <th scope="col">Is Header</th>
                                <th scope="col">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fileRows as $fileRow)
                                <tr>
                                    <td>{{ $fileRow->row_number }}</td>
                                    <td>{{ $fileRow->is_header ? 'Yes' : 'No' }}</td>
                                    <td class="text-break">
                                        <pre class="mb-0 text-break" style="white-space: pre-wrap;">{{ json_encode($fileRow->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-secondary fst-italic">No stored rows.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($fileRows->hasPages())
                    <div class="mt-3">
                        {{ $fileRows->links() }}
                    </div>
                @endif
            @endif
        </div>

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
