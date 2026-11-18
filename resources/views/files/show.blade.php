@extends('layouts.app')

@section('title', e($file->filename))

@section('content')
    <div class="container py-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('files.index') }}">Files</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $file->filename }}</li>
            </ol>
        </nav>

        <h1 class="h3 mb-4">File Upload {{ $file->id }}</h1>

        <section class="mb-4">
            <h2 class="h4">File Upload</h2>

            <dl class="row mb-0">
                <dt class="col-sm-3">Record type</dt>
                <dd class="col-sm-9">{{ ucfirst($file->record_type) }}</dd>

                <dt class="col-sm-3">Filename</dt>
                <dd class="col-sm-9">{{ $file->filename }}</dd>

                <dt class="col-sm-3">Path</dt>
                <dd class="col-sm-9"><code>{{ $file->filepath }}</code></dd>

                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9">{{ $file->created_at?->format('d M Y H:i') }}</dd>

                <dt class="col-sm-3">Updated</dt>
                <dd class="col-sm-9 mb-0">{{ $file->updated_at?->format('d M Y H:i') }}</dd>
            </dl>
        </section>

        <section class="mb-4">
            <h2 class="h4">File Rows</h2>

            <p class="mb-3">CSV parsed: <strong>{{ $file->is_parsed ? 'Yes' : 'No' }}</strong></p>

            <p class="mb-3">Parse attempts: <strong>{{ $parseAttemptCount }}</strong></p>

            @if ($lastAttempt !== null)
                <p class="mb-3">
                    Last attempt: <span class="badge text-bg-{{ $lastAttempt->status === 'completed' ? 'success' : ($lastAttempt->status === 'error' ? 'danger' : 'secondary') }}">{{ ucfirst($lastAttempt->status) }}</span> {{ $lastAttempt->created_at?->format('d M Y H:i') }}
                </p>
            @endif

            @if ($lastAttempt?->status === 'error')
                <div class="alert alert-danger py-2 mb-3" role="alert">{{ $lastAttempt->message }}</div>
            @endif

            @if ($file->is_parsed)
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
            @endif

            @if ($totalFileRows === 0)
                <p class="text-secondary fst-italic">This CSV has not been parsed yet.</p>
            @endif

            <a class="btn {{ $file->is_parsed ? 'btn-outline-primary' : 'btn-primary' }} btn-sm" href="{{ route('file_rows.index', $file) }}">Open File Rows</a>
        </section>

        <section class="mb-4">
            <h2 class="h4">File Mappings</h2>
            <p class="text-secondary fst-italic mb-0">Added in a later phase.</p>
        </section>
    </div>
@endsection
