<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · Bon Appétit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/panel.css', 'resources/js/panel.js'])
</head>
<body data-page="login">
    <div class="login">
        <section class="login-brand" aria-label="Bon Appétit">
            <div>
                <span class="login-logo"><x-icon name="cubiertos" /></span>
                <h1>Bon Appétit</h1>
                <span class="brand-rule"></span>
                <p>Gestión de mesas, comandas y menú del restaurante.</p>
            </div>
        </section>

        <main class="login-form-side">
            <div class="login-box">
                <h2>Bienvenido de nuevo</h2>
                <p class="lead">Iniciá sesión con tu cuenta del restaurante.</p>

                <form class="panel-card login-card" method="post" action="#" data-form-login novalidate>
                    <div class="field">
                        <label for="email">Correo electrónico</label>
                        <input id="email" name="email" type="email" autocomplete="username" required placeholder="nombre@bonappetit.test">
                    </div>
                    <div class="field">
                        <label for="password">Contraseña</label>
                        <div class="password-wrap">
                            <input id="password" name="password" type="password" autocomplete="current-password" required>
                            <button type="button" class="icon-btn" data-ver-password aria-label="Mostrar contraseña" aria-pressed="false">
                                <x-icon name="ojo" />
                            </button>
                        </div>
                    </div>
                    <div class="login-options">
                        <label><input type="checkbox" name="recordar" data-recordar> Recordarme</label>
                    </div>
                    <p class="field-error" data-login-error role="alert" hidden></p>
                    <button type="submit" class="btn btn--primary btn--block">Iniciar sesión</button>
                </form>

                <p class="login-help">¿Problemas para ingresar? Contactá al administrador del sistema.</p>
            </div>
        </main>
    </div>
</body>
</html>
