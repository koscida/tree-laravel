<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
            <div class="container">
                <a class="navbar-brand fw-semibold" href="{{ url('/') }}">Tree</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false"
                        aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-auto">
                        @foreach (config('navigation', []) as $link)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}"
                                   href="{{ route($link['route']) }}"
                                   @if (request()->routeIs($link['route'])) aria-current="page" @endif>
                                    {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>

        <main class="flex-grow-1">
            @yield('content')
        </main>

        <footer class="border-top py-3 mt-auto">
            <div class="container text-center text-secondary small">
                {{ config('app.name', 'Laravel') }} &middot; {{ date('Y') }}
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
