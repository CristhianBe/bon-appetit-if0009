@php
    $enlaces = [
        ['ruta' => 'mesas', 'texto' => 'Mesas', 'icono' => 'mesas'],
        ['ruta' => 'pedidos', 'texto' => 'Comandas', 'icono' => 'comandas'],
        ['ruta' => 'reservas', 'texto' => 'Reservaciones', 'icono' => 'reservas'],
    ];
    $admin = [
        ['ruta' => 'menu', 'texto' => 'Menú y catálogo', 'icono' => 'menu'],
    ];
    $movil = [
        ['ruta' => 'mesas', 'texto' => 'Mesas', 'icono' => 'mesas'],
        ['ruta' => 'pedidos', 'texto' => 'Comandas', 'icono' => 'comandas'],
        ['ruta' => 'menu', 'texto' => 'Menú', 'icono' => 'menu'],
        ['ruta' => 'reservas', 'texto' => 'Reservas', 'icono' => 'reservas'],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') · Bon Appétit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/panel.css', 'resources/js/panel.js'])
    @stack('head')
</head>
<body data-page="@yield('page')">
    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <a class="brand-name" href="{{ route('mesas') }}">Bon Appétit</a>
                <span class="brand-rule"></span>
            </div>

            <nav aria-label="Navegación principal">
                <p class="nav-section">Operación</p>
                @foreach ($enlaces as $e)
                    <a href="{{ route($e['ruta']) }}" @class(['nav-link', 'is-active' => request()->routeIs($e['ruta'])]) @if (request()->routeIs($e['ruta'])) aria-current="page" @endif>
                        <x-icon :name="$e['icono']" /> {{ $e['texto'] }}
                    </a>
                @endforeach

                <p class="nav-section">Administración</p>
                @foreach ($admin as $e)
                    <a href="{{ route($e['ruta']) }}" @class(['nav-link', 'is-active' => request()->routeIs($e['ruta'])]) @if (request()->routeIs($e['ruta'])) aria-current="page" @endif>
                        <x-icon :name="$e['icono']" /> {{ $e['texto'] }}
                    </a>
                @endforeach
            </nav>

            <div class="nav-bottom">
                <button type="button" class="nav-link nav-link--button" data-logout>
                    <x-icon name="salir" /> Cerrar sesión
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <h1 class="topbar-title">@yield('heading')</h1>
                <div class="topbar-meta">
                    <span class="session">Sesión activa</span>
                    <div class="user-chip">
                        <span class="avatar" data-user-initials aria-hidden="true">BA</span>
                        <span>
                            <strong data-user-name>Usuario</strong>
                            <small>Turno tarde</small>
                        </span>
                    </div>
                    <button type="button" class="icon-btn topbar-logout" data-logout aria-label="Cerrar sesión">
                        <x-icon name="salir" />
                    </button>
                </div>
            </header>

            <main id="contenido" class="content" tabindex="-1">
                @yield('content')
            </main>

            <footer class="footer">&copy; {{ date('Y') }} Bon Appétit · Sistema de gestión del restaurante</footer>
        </div>
    </div>

    <nav class="bottom-nav" aria-label="Navegación móvil">
        @foreach ($movil as $e)
            <a href="{{ route($e['ruta']) }}" @class(['is-active' => request()->routeIs($e['ruta'])]) @if (request()->routeIs($e['ruta'])) aria-current="page" @endif>
                <x-icon :name="$e['icono']" /> {{ $e['texto'] }}
            </a>
        @endforeach
    </nav>

    <div class="toast" role="status" aria-live="polite" data-toast>
        <x-icon name="info" /> <span data-toast-text></span>
    </div>

    @stack('scripts')
</body>
</html>
