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
                <li class="breadcrumb-item active" aria-current="page">Import Family</li>
            </ol>
        </nav>

        <h1 class="h3 mb-4">Import Family</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h5 card-title">Choose a surname</h2>

                <div class="row g-3 align-items-start">
                    <div class="col-md-6">
                        <label for="surname" class="form-label">Surname</label>
                        <input type="text" name="surname" class="form-control" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
