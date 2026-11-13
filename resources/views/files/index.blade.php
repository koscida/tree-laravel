@extends('layouts.app')

@section('title', 'Files')

@section('content')
    <div class="container py-5">
        <h1 class="h3 mb-4">Files</h1>

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
                            <th scope="col">Path</th>
                            <th scope="col">Created</th>
                            <th scope="col">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($files as $file)
                            <tr>
                                <td>{{ $file->filename }}</td>
                                <td><code>{{ $file->filepath }}</code></td>
                                <td>{{ $file->created_at?->format('d M Y') }}</td>
                                <td>{{ $file->updated_at?->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
