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
            <p class="text-secondary fst-italic mb-0">Added in a later phase.</p>
        </section>

        <section class="mb-4">
            <h2 class="h4">File Mappings</h2>
            <p class="text-secondary fst-italic mb-0">Added in a later phase.</p>
        </section>
    </div>
@endsection
