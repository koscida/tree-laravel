@extends('layouts.app')

@section('title', 'Imports')

@section('content')
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('file_imports.index') }}">File Imports</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('file_imports.show', [ 'fileUploadId' => $file->id ]) }}">{{ $file->filename }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Import Surnames</li>
            </ol>
        </nav>

        <h1 class="h3 mb-4">Import Surnames</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 card-title">Surnames in the file</h2>

                        @if ($surnameCandidates === [])
                            <p class="text-secondary fst-italic mb-0">No surnames were found in the mapped child and parent name columns.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Surname</th>
                                            <th scope="col">Rows</th>
                                            <th scope="col">Connections</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($surnameCandidates as $candidate)
                                            <tr>
                                                <td>{{ $candidate['surname'] }}</td>
                                                <td>{{ $candidate['row_count'] }}</td>
                                                <td>{{ $candidate['connection_count'] }}</td>
                                                <td>
                                                    <div class="d-flex flex-column gap-2">
                                                        <form method="POST" action="{{ route('file_imports.surname_store', $file) }}">
                                                            @csrf
                                                            <input type="hidden" name="action" value="surname">
                                                            <input type="hidden" name="surname" value="{{ $candidate['surname'] }}">
                                                            <button type="submit" class="btn btn-sm btn-primary">Import Surname</button>
                                                        </form>

                                                        <form method="POST" action="{{ route('file_imports.surname_store', $file) }}">
                                                            @csrf
                                                            <input type="hidden" name="action" value="alternative">
                                                            <input type="hidden" name="surname" value="{{ $candidate['surname'] }}">
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" name="canonical_id" class="form-control"
                                                                       placeholder="Canonical id" aria-label="Canonical id"
                                                                       value="{{ old('canonical_id') }}">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary">Import as alternative spelling</button>
                                                            </div>
                                                            @error('canonical_id')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 card-title">Imported surnames</h2>

                        @if ($importedSurnames->isEmpty())
                            <p class="text-secondary fst-italic mb-0">No surnames have been imported for this file.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Id</th>
                                            <th scope="col">Surname</th>
                                            <th scope="col">Canonical Id</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($importedSurnames as $surname)
                                            <tr>
                                                <td>{{ $surname->id }}</td>
                                                <td>{{ $surname->surname }}</td>
                                                <td>{{ $surname->canonical_surname_id ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
