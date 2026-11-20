@extends('layouts.app')

@section('title', 'Imports')

@section('content')
    <div class="container py-5">
        <h1 class="h3 mb-4">Imports</h1>

        <div class="table-responsive">
            <table class="table table-striped align-middle" id="file-uploads-table">
                <thead>
                    <tr>
                        <th scope="col">Filename</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fileUploads as $file)
                        <tr>
                            <td>{{ $file->filename }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('file_imports.show', $file) }}">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
