@extends('layouts.app')

@section('content')

<div class="row justify-content-center">

    <div class="col-md-8">

        <div class="card">

            <div class="card-header">

                <h4>
                    CSV Upload
                </h4>

            </div>

            <div class="card-body">

                @if(session('success'))

                    <div class="alert alert-success">

                        {{ session('success') }}

                    </div>

                @endif

                @if($errors->any())

                    <div class="alert alert-danger">

                        <ul class="mb-0">

                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif

                <form method="POST"
                      action="{{ route('upload.store') }}"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="mb-3">

                        <label class="form-label">

                            Upload CSV File

                        </label>

                        <input
                            type="file"
                            name="csv_file"
                            class="form-control"
                            accept=".csv"
                            required>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        Start Import

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection