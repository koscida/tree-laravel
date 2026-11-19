@extends('layouts.app')

@section('title', 'File Settings')

@section('content')
    <div class="container py-5">
        <h1 class="h3 mb-4">File Settings</h1>

        <section class="mb-5">
            <h2 class="h4">Relationships</h2>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Key</th>
                            <th scope="col">Label</th>
                            <th scope="col">Is Multiple People</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($relationships as $relationship)
                            <tr>
                                <td>{{ $relationship->key }}</td>
                                <td>{{ $relationship->label }}</td>
                                <td>
                                    @if($relationship->is_multiple_people)
                                        <span class="badge text-bg-info">Yes</span>
                                    @else
                                        <span class="badge text-bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-secondary">Data has not been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="h4">Name Parts</h2>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Key</th>
                            <th scope="col">Label</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($nameParts as $namePart)
                            <tr>
                                <td>{{ $namePart->key }}</td>
                                <td>{{ $namePart->label }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-secondary">Data has not been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="h4">Milestones</h2>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Key</th>
                            <th scope="col">Label</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($milestones as $milestone)
                            <tr>
                                <td>{{ $milestone->key }}</td>
                                <td>{{ $milestone->label }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-secondary">Data has not been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="h4">Date Parts</h2>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Key</th>
                            <th scope="col">Label</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dateParts as $datePart)
                            <tr>
                                <td>{{ $datePart->key }}</td>
                                <td>{{ $datePart->label }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-secondary">Data has not been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="h4">Source Parts</h2>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Key</th>
                            <th scope="col">Label</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sourceParts as $sourcePart)
                            <tr>
                                <td>{{ $sourcePart->key }}</td>
                                <td>{{ $sourcePart->label }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-secondary">Data has not been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="h4">Record Labels</h2>

            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Key</th>
                            <th scope="col">Label</th>
                            <th scope="col">Data Format</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recordLabels as $recordLabel)
                            <tr>
                                <td>{{ $recordLabel->key }}</td>
                                <td>{{ $recordLabel->label }}</td>
                                <td>{{ $recordLabel->data_format }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-secondary">Data has not been added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
