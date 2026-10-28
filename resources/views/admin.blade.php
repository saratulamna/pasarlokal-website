
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#193B2C">

    <title>@yield('title', 'Admin Studio') — PasarLokal</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('page-styles')
</head>

<body class="admin-dashboard-page">

<div class="admin-shell" id="adminShell">

    <div
        class="admin-mobile-overlay"
        id="adminMobileOverlay"
    ></div>

    {{-- SIDEBAR BERSAMA --}}
    @include('admin.partials.sidebar')

    {{-- AREA UTAMA --}}
    <main class="admin-main">

        {{-- TOPBAR BERSAMA --}}
        <header class="admin-topbar">

            <div class="admin-topbar-left">

                <button
                    class="admin-mobile-toggle"
                    type="button"
                    id="adminMobileToggle"
                    aria-label="Buka navigasi"
                    aria-controls="adminSidebar"
                >
                    ☰
                </button>

                <div class="admin-breadcrumb">
                    <span>Workspace</span>
                    <b>/</b>
                    <strong>@yield('breadcrumb', 'Admin')</strong>
                </div>

            </div>

            <div class="admin-topbar-actions">

                <span class="admin-system-status">
                    <i></i>
                    SYSTEM ONLINE
                </span>

                <span class="admin-topbar-divider"></span>

                <span class="admin-date">
                    {{ now()->translatedFormat('D, d M Y') }}
                </span>

                <button
                    type="button"
                    id="adminRefreshButton"
                    class="admin-refresh-button"
                    title="Muat ulang halaman"
                    aria-label="Muat ulang halaman"
                >
                    ↻
                </button>

            </div>

        </header>

        {{-- KONTEN HALAMAN --}}
        <div class="admin-content">
            @yield('content')
        </div>

    </main>

</div>

@stack('page-scripts')

</body>
</html>
