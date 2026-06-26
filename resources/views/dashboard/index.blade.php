@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
    <h2 class="mb-0">Dashboard</h2>
    <small class="text-muted">
        Shopify Product Import Overview
    </small>
</div>

<a href="{{ route('upload.form') }}"
   class="btn btn-primary">
    Upload CSV
</a>

</div>

@if(session('success')) <div class="alert alert-success alert-dismissible fade show">
{{ session('success') }}

    <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
    </button>
</div>

@endif

<div class="row mb-4">

<div class="col-lg-3 col-md-6 mb-3">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
            <small class="text-muted">
                Total Uploads
            </small>

            <h2 class="fw-bold mb-0">
                {{ $stats['uploads'] }}
            </h2>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 mb-3">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
            <small class="text-muted">
                Total Products
            </small>

            <h2 class="fw-bold mb-0">
                {{ $stats['products'] }}
            </h2>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 mb-3">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
            <small class="text-muted">
                Successful Imports
            </small>

            <h2 class="fw-bold text-success mb-0">
                {{ $stats['success'] }}
            </h2>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 mb-3">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
            <small class="text-muted">
                Failed Imports
            </small>

            <h2 class="fw-bold text-danger mb-0">
                {{ $stats['failed'] }}
            </h2>
        </div>
    </div>
</div>

</div>

<div class="card shadow-sm border-0 mb-5">

<div class="card-header bg-white">
    <h5 class="mb-0">
        Recent Uploads
    </h5>
</div>

<div class="card-body">

    @if($uploads->count())

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                <tr>

                    <th>ID</th>

                    <th>File</th>

                    <th>Status</th>

                    <th>Progress</th>

                    <th>Success</th>

                    <th>Failed</th>

                    <th>Created</th>

                    <th>Action</th>

                </tr>

                </thead>

                <tbody>

                @foreach($uploads as $upload)

                    @php
                        $percentage = $upload->total_records > 0
                            ? round(($upload->processed_records / $upload->total_records) * 100)
                            : 0;
                    @endphp

                    <tr>

                        <td>
                            {{ $upload->id }}
                        </td>

                        <td>
                            {{ $upload->original_filename }}
                        </td>

                        <td>

                            @switch($upload->status)

                                @case('completed')
                                    <span class="badge bg-success">
                                        Completed
                                    </span>
                                    @break

                                @case('processing')
                                    <span class="badge bg-warning text-dark">
                                        Processing
                                    </span>
                                    @break

                                @case('failed')
                                    <span class="badge bg-danger">
                                        Failed
                                    </span>
                                    @break

                                @default
                                    <span class="badge bg-secondary">
                                        Pending
                                    </span>

                            @endswitch

                        </td>

                        <td style="min-width:200px;">

                            <div class="progress">

                                <div class="progress-bar"
                                     role="progressbar"
                                     style="width: {{ $percentage }}%;">

                                    {{ $percentage }}%

                                </div>

                            </div>

                        </td>

                        <td>
                            {{ $upload->successful_records }}
                        </td>

                        <td>
                            {{ $upload->failed_records }}
                        </td>

                        <td>
                            {{ $upload->created_at->diffForHumans() }}
                        </td>

                        <td>

                            <a href="{{ route('imports.show', $upload) }}"
                               class="btn btn-sm btn-outline-primary">

                                View

                            </a>

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

        {{ $uploads->links() }}

    @else

        <div class="text-center py-5">

            <h5>No Uploads Yet</h5>

            <p class="text-muted">
                Upload your first CSV file to begin importing products.
            </p>

            <a href="{{ route('upload.form') }}"
               class="btn btn-primary">
                Upload CSV
            </a>

        </div>

    @endif

</div>

</div>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white">
        <h5 class="mb-0">Product Import Status</h5>
    </div>
    <div class="card-body">
        @if($products->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            {{ $product->id }}
                        </td>
                        <td>
                            {{ $product->title }}
                        </td>
                        <td>
                            {{ $product->sku }}
                        </td>
                        <td>
                            {{ $product->price }}
                        </td>
                        <td>
                            @if($product->status === 'success')
                                <span class="badge bg-success">
                                    Success
                                </span>
                            @elseif($product->status === 'failed')
                                <span class="badge bg-danger">
                                    Failed
                                </span>
                            @elseif($product->status === 'processing')
                                <span class="badge bg-warning">
                                    Processing
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td>
                            {{ $product->error_message ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <p class="text-muted mb-0">
                No products imported yet.
            </p>
        </div>
        @endif
    </div>
</div>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            Recent Errors
        </h5>
    </div>
    <div class="card-body">
        @if($errors->count())
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Source</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($errors as $error)
                    <tr>
                        <td>
                            {{ $error->id }}
                        </td>

                        <td>
                            {{ $error->source }}
                        </td>
                        <td>
                            <span title="{{ $error->message }}">
                                {{ \Illuminate\Support\Str::limit($error->message, 80) }}
                            </span>
                        </td>
                        <td>
                            {{ $error->created_at->diffForHumans() }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <p class="text-muted mb-0">
                No errors recorded.
            </p>
        </div>
        @endif
    </div>
</div>
@endsection