@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">


<div>

    <h2 class="mb-0">
        System Logs
    </h2>

    <small class="text-muted">
        Import events and application errors
    </small>

</div>


</div>

<div class="card shadow-sm border-0">


<div class="card-header bg-white">

    <h5 class="mb-0">
        Error & Activity Logs
    </h5>

</div>

<div class="card-body">

    @if($logs->count())

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                <tr>

                    <th>ID</th>

                    <th>Source</th>

                    <th>Message</th>

                    <th>Context</th>

                    <th>Date</th>

                </tr>

                </thead>

                <tbody>

                @foreach($logs as $log)

                    <tr>

                        <td>
                            {{ $log->id }}
                        </td>

                        <td>

                            @php

                                $sourceClass = match(strtolower($log->source)) {

                                    'shopify' => 'bg-primary',

                                    'csv' => 'bg-info',

                                    'upload' => 'bg-success',

                                    'queue' => 'bg-warning text-dark',

                                    default => 'bg-secondary'

                                };

                            @endphp

                            <span class="badge {{ $sourceClass }}">

                                {{ ucfirst($log->source) }}

                            </span>

                        </td>

                        <td>

                            <span title="{{ $log->message }}">

                                {{ \Illuminate\Support\Str::limit($log->message, 100) }}

                            </span>

                        </td>

                        <td>

                            @if($log->context)

                                <button
                                    class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#contextModal{{ $log->id }}">

                                    View

                                </button>

                                <div class="modal fade"
                                     id="contextModal{{ $log->id }}"
                                     tabindex="-1">

                                    <div class="modal-dialog modal-lg">

                                        <div class="modal-content">

                                            <div class="modal-header">

                                                <h5 class="modal-title">
                                                    Log Context
                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal">
                                                </button>

                                            </div>

                                            <div class="modal-body">


                                                <pre class="bg-light p-3 rounded">{{ json_encode(
                                                json_decode($log->context, true),
                                                JSON_PRETTY_PRINT
                                                ) }}</pre>


                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @else

                                <span class="text-muted">
                                    —
                                </span>

                            @endif

                        </td>

                        <td>

                            <div>
                                {{ $log->created_at->format('d M Y') }}
                            </div>

                            <small class="text-muted">
                                {{ $log->created_at->format('h:i A') }}
                            </small>

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

        <div class="mt-3">

            {{ $logs->links() }}

        </div>

    @else

        <div class="text-center py-5">

            <h5>
                No Logs Available
            </h5>

            <p class="text-muted mb-0">
                System logs will appear here when import activity occurs.
            </p>

        </div>

    @endif

</div>


</div>

@endsection
