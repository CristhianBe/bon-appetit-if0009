<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon Appétit - @yield('title', 'Restaurante')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- CSS global -->
    @vite(['resources/css/app.css'])
</head>
<body>
    <header class="container" style="padding: var(--space-sm) 0; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0; font-size: var(--text-2xl); color: var(--color-primary);">Bon Appétit</h1>
        <nav>
            <a href="{{ route('home') }}" style="margin-right: var(--space-sm); text-decoration: none; color: var(--color-text);">Inicio</a>
            <a href="{{ route('menu') }}" style="margin-right: var(--space-sm); text-decoration: none; color: var(--color-text);">Menú</a>
            <a href="{{ route('contact') }}" style="text-decoration: none; color: var(--color-text);">Contacto</a>
        </nav>
    </header>

    <main class="container" style="min-height: 70vh; padding: var(--space-md) 0;">
        @yield('content')
    </main>

    <footer class="container" style="text-align: center; padding: var(--space-md) 0; border-top: 1px solid var(--color-border); margin-top: var(--space-xl); color: var(--color-text-muted);">
        <p>&copy; {{ date('Y') }} Bon Appétit. Todos los derechos reservados.</p>
    </footer>
</body>
</html>