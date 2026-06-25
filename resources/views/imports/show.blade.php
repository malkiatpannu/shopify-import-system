@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">


<div>

    <h2 class="mb-0">
        Import Details
    </h2>

    <small class="text-muted">
        File: {{ $upload->original_filename }}
    </small>

</div>

</div>

<div class="row mb-4">


<div class="col-md-3 mb-3">

    <div class="card shadow-sm border-0 h-100">

        <div class="card-body">

            <small class="text-muted">
                Total Records
            </small>

            <h3 class="mb-0">
                {{ $upload->total_records }}
            </h3>

        </div>

    </div>

</div>

<div class="col-md-3 mb-3">

    <div class="card shadow-sm border-0 h-100">

        <div class="card-body">

            <small class="text-muted">
                Processed
            </small>

            <h3 class="mb-0">
                {{ $upload->processed_records }}
            </h3>

        </div>

    </div>

</div>

<div class="col-md-3 mb-3">

    <div class="card shadow-sm border-0 h-100">

        <div class="card-body">

            <small class="text-muted">
                Successful
            </small>

            <h3 class="text-success mb-0">
                {{ $upload->successful_records }}
            </h3>

        </div>

    </div>

</div>

<div class="col-md-3 mb-3">

    <div class="card shadow-sm border-0 h-100">

        <div class="card-body">

            <small class="text-muted">
                Failed
            </small>

            <h3 class="text-danger mb-0">
                {{ $upload->failed_records }}
            </h3>

        </div>

    </div>

</div>


</div>

<div class="card shadow-sm border-0 mb-4">


<div class="card-header bg-white">

    <h5 class="mb-0">
        Upload Information
    </h5>

</div>

<div class="card-body">

    <div class="row">

        <div class="col-md-4 mb-3">

            <strong>File Name</strong>

            <div>
                {{ $upload->original_filename }}
            </div>

        </div>

        <div class="col-md-4 mb-3">

            <strong>Status</strong>

            <div>

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

            </div>

        </div>

        <div class="col-md-4 mb-3">

            <strong>Uploaded At</strong>

            <div>
                {{ $upload->created_at->format('d M Y h:i A') }}
            </div>

        </div>

    </div>

    @php
        $percentage = $upload->total_records > 0
            ? round(($upload->processed_records / $upload->total_records) * 100)
            : 0;
    @endphp

    <div class="mt-3">

        <label class="form-label fw-semibold">
            Import Progress
        </label>

        <div class="progress" style="height: 25px;">

            <div class="progress-bar"
                 role="progressbar"
                 style="width: {{ $percentage }}%;">

                {{ $percentage }}%

            </div>

        </div>

    </div>

</div>


</div>

<div class="card shadow-sm border-0">


<div class="card-header bg-white d-flex justify-content-between align-items-center">

    <h5 class="mb-0">
        Imported Products
    </h5>

    <span class="badge bg-primary">
        {{ $imports->total() }} Products
    </span>

</div>

<div class="card-body">

    @if($imports->count())

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                <tr>

                    <th>ID</th>

                    <th>Action</th>
                    <th>Title</th>

                    <th>SKU</th>

                    <th>Price</th>

                    <th>Status</th>

                    <th>Shopify ID</th>

                    <th>Error</th>

                </tr>

                </thead>

                <tbody>

                @foreach($imports as $import)

                    @php
                        $product = $import->product;
                    @endphp
                    <tr>

                        <td>
                            {{ $product->id }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $import->action === 'create' ? 'info' : 'primary' }}">
                            {{ $import->action }}d
                            </span>
                        </td>
                        <td>
                            {{ $product->title }}
                        </td>

                        <td>
                            {{ $product->sku }}
                        </td>

                        <td>
                            ₹{{ number_format($product->price, 2) }}
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

                                <span class="badge bg-warning text-dark">
                                    Processing
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    Pending
                                </span>

                            @endif

                        </td>

                        <td>

                            @if($product->shopify_product_id)

                                <span class="text-success">
                                    {{ $product->shopify_product_id }}
                                </span>

                            @else

                                <span class="text-muted">
                                    —
                                </span>

                            @endif

                        </td>

                        <td>

                            @if($product->error_message)

                                <span class="text-danger"
                                      title="{{ $product->error_message }}">

                                    {{ \Illuminate\Support\Str::limit($product->error_message, 60) }}

                                </span>

                            @else

                                <span class="text-muted">
                                    —
                                </span>

                            @endif

                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

        <div class="mt-3">

            {{ $imports->links() }}

        </div>

    @else

        <div class="text-center py-5">

            <h5>
                No Products Found
            </h5>

            <p class="text-muted mb-0">
                No products have been imported for this upload.
            </p>

        </div>

    @endif

</div>


</div>

@endsection
