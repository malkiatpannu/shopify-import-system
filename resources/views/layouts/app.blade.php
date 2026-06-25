<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify Import System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        .status-badge {
            min-width: 90px;
        }
    </style>
</head>

<body>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">

            <a class="navbar-brand"
               href="{{ route('dashboard') }}">
                <img src="/shopify.png"
                     width="100"
                     height="50"
                     class="d-inline-block align-top"
                     alt="Laravel Logo">
            </a>

            <button class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">

                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse"
                 id="navbarNav">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('upload.*') ? 'active' : '' }}"
                           href="{{ route('upload.form') }}">
                            Upload CSV
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('logs.*') ? 'active' : '' }}"
                           href="{{ route('logs.index') }}">
                            Logs
                        </a>
                    </li>

                </ul>

                <span class="navbar-text text-light">
                    Laravel 12 Assessment
                </span>

            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="container mt-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>
            </div>
        @endif


    </div>

    {{-- Main Content --}}
    <main class="container pb-5">

        @yield('content')

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const fileInput = document.querySelector(
                'input[name="csv_file"]'
            );

            if (!fileInput) {
                return;
            }

            fileInput.addEventListener('change', function () {

                const file = this.files[0];

                if (!file) {
                    return;
                }

                const maxSize = 5 * 1024 * 1024;

                if (file.size > maxSize) {

                    alert('File size cannot exceed 5MB');

                    this.value = '';

                    return;
                }

                if (!file.name.toLowerCase().endsWith('.csv')) {

                    alert('Only CSV files are allowed');

                    this.value = '';
                }
            });
        });
    </script>

    @stack('scripts')

</body>

</html>