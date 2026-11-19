@extends('layouts.app')

@section('title', 'Files')

@section('content')
    <div class="container py-5">
        <h1 class="h3 mb-4">Files</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 card-title">Upload a file</h2>

                <form method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="row g-3 align-items-start">
                        <div class="col-md-6">
                            <label for="file" class="form-label">CSV file</label>
                            <input type="file" id="file" name="file" accept=".csv,text/csv"
                                   class="form-control @error('file') is-invalid @enderror" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum size 50 MB.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="record_type" class="form-label">Record type</label>
                            <select id="record_type" name="record_type"
                                    class="form-select @error('record_type') is-invalid @enderror" required>
                                <option value="" @selected(old('record_type', '') === '')>Choose&hellip;</option>
                                @foreach (['birth', 'marriage', 'death'] as $type)
                                    <option value="{{ $type }}" @selected(old('record_type') === $type)>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('record_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Upload</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($files->isEmpty())
            <div class="alert alert-info mb-0" role="alert">
                No files uploaded yet
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Filename</th>
                            <th scope="col">Type</th>
                            <th scope="col">Path</th>
                            <th scope="col">Is parsed</th>
                            <th scope="col">Is mapped</th>
                            <th scope="col">Created</th>
                            <th scope="col">Updated</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($files as $file)
                            <tr>
                                <td>{{ $file->filename }}</td>
                                <td>{{ ucfirst($file->record_type) }}</td>
                                <td><code>{{ $file->filepath }}</code></td>
                                <td>{{ $file->is_parsed ? "Yes" : "No" }}</td>
                                <td>{{ $file->is_mapped ? "Yes" : "No" }}</td>
                                <td>{{ $file->created_at?->format('d M Y') }}</td>
                                <td>{{ $file->updated_at?->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary"
                                       href="{{ route('files.show', $file) }}"
                                       aria-label="Open {{ $file->filename }}">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
