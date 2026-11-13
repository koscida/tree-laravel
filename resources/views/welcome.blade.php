<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet">
        @endif
    </head>
    <body class="bg-light d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
            <div class="container">
                <a class="navbar-brand fw-semibold" href="/">{{ config('app.name', 'Laravel') }}</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                        aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://laravel.com/docs" target="_blank" rel="noopener">Docs</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                               aria-expanded="false">Resources</a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="https://laracasts.com" target="_blank" rel="noopener">Laracasts</a></li>
                                <li><a class="dropdown-item" href="https://getbootstrap.com/docs/5.3/" target="_blank" rel="noopener">Bootstrap docs</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="container py-5 flex-grow-1">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-lg-8">
                    <span class="badge text-bg-primary mb-3">Bootstrap 5.3.9</span>
                    <h1 class="display-5 fw-bold">{{ config('app.name', 'Laravel') }}</h1>
                    <p class="lead text-secondary">
                        Your Laravel application is running on Bootstrap, compiled through Vite.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <a href="https://laravel.com/docs" class="btn btn-primary btn-lg" target="_blank" rel="noopener">
                            Laravel docs
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-lg"
                                data-bs-toggle="modal" data-bs-target="#welcomeModal">
                            Test Bootstrap JS
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h2 class="h5 card-title">Routes</h2>
                            <p class="card-text text-secondary">
                                Define your application URLs in <code>routes/web.php</code>.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h2 class="h5 card-title">Views</h2>
                            <p class="card-text text-secondary">
                                Blade templates live in <code>resources/views</code>.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h2 class="h5 card-title">Database</h2>
                            <p class="card-text text-secondary">
                                Models and migrations are in <code>app/Models</code> and <code>database/</code>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-top py-4">
            <div class="container text-center text-secondary small">
                Laravel {{ Illuminate\Foundation\Application::VERSION }} &middot; PHP {{ PHP_VERSION }}
            </div>
        </footer>

        <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="welcomeModalLabel">Bootstrap JS works</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        This modal, the navbar toggle and the dropdown are powered by
                        <code>bootstrap.bundle.min.js</code> loaded through Vite.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
