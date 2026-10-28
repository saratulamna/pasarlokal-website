<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — PasarLokal</title>


@vite([
    'resources/css/app.css',
    'resources/css/portal.css',
    'resources/css/pages/admin/umkms.css',
    'resources/js/portal.js'
])

@stack('page-styles')

</head>

<body class="portal-body">
    @php($currentUser = auth()->user())

    <div class="portal-app">
        <aside class="portal-sidebar" id="portalSidebar">
            @if ($currentUser->role === 'admin')
                @include('admin.partials.sidebar')
            @elseif ($currentUser->role === 'umkm')
                @include('umkm.partials.sidebar')
            @else
                @include('pembeli.partials.sidebar')
            @endif
        </aside>

        <div class="portal-workspace">
            <header class="portal-topbar">
                <div class="topbar-left">
                    <button
                        type="button"
                        class="mobile-menu-toggle"
                        data-sidebar-toggle
                        aria-label="Buka menu"
                        aria-controls="portalSidebar"
                        aria-expanded="false"
                    >
                        ☰
                    </button>

                    <div>
                        <p class="topbar-eyebrow">RUANG KERJA</p>
                        <p class="topbar-greeting">
                            Halo, {{ $currentUser->name }}!
                        </p>
                    </div>
                </div>

                <div class="topbar-actions">
                    <span class="role-badge">
                        {{ strtoupper($currentUser->role) }}
                    </span>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf

                        <button class="logout-button" type="submit">
                            Keluar <span aria-hidden="true">↗</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="portal-content">
                @if (session('success'))
                    <div class="portal-alert" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="portal-footer">
                <span>PasarLokal</span>
                <span>Produk lokal, peluang lebih luas.</span>
            </footer>
        </div>
    </div>
    @stack('page-scripts')
</body>
</html>