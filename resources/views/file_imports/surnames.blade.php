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

        <div class="card mb-4">
            <div class="card-body">
            </div>
        </div>
    </div>
@endsection
