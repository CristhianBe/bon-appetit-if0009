<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú de Platillos - Bon Appétit</title>
    <style>
        <?php include resource_path('css/app.css'); ?>

        /* ==========================================================================
           JUSTIFICACIÓN DE MAQUETACIÓN - PANTALLA 2 (REQUISITO LABORATORIO 8)
           - CSS Grid: Se utiliza para maquetar la cuadrícula bidimensional de platillos
             (.catalog-grid), adaptándose automáticamente al ancho de pantalla disponible.
           - Flexbox: Se utiliza para alinear verticalmente los elementos dentro de
             cada tarjeta (.card-body) y colocar precio y botón alineados abajo.
           ========================================================================== */

        body {
            background-color: #fdf8f5;
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .menu-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .menu-container h1 {
            color: #5a1217;
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Cuadrícula del Menú en CSS Grid (3 columnas en escritorio) */
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        /* Tarjeta de Platillo estilizada con Flexbox */
        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid #eee;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover,
        .card:focus-within {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .card-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }

        .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-title {
            color: #5a1217;
            margin: 0 0 10px 0;
            font-size: 1.3rem;
        }

        .card-desc {
            font-size: 0.95rem;
            color: #666;
            margin: 0 0 15px 0;
            line-height: 1.4;
            flex-grow: 1;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #f0f0f0;
        }

        .card-price {
            font-size: 1.25rem;
            font-weight: bold;
            color: #5a1217;
        }

        .btn-card {
            background-color: #5a1217;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease;
        }

        .btn-card:hover,
        .btn-card:focus {
            background-color: #8b0000;
            outline: 2px solid #5a1217;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <nav class="nav-bar" aria-label="Navegación principal">
            <span class="logo">Bon Appétit</span>
            <ul>
                <li><a href="/mesas">Mesas</a></li>
                <li><a href="/menu" aria-current="page">Menú</a></li>
                <li><a href="/pedidos">Pedidos</a></li>
                <li><a href="/reservas">Reservaciones</a></li>
                <li><a href="/login">Salir</a></li>
            </ul>
        </nav>
    </header>

    <main class="menu-container">
        <h1>Nuestra Carta</h1>

        <section class="catalog-grid" aria-label="Catálogo de platillos disponibles">
            <!-- 1. Ensalada Gourmet -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80" alt="Ensalada Gourmet" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Ensalada Gourmet</h2>
                    <p class="card-desc">Ingredientes frescos de estación acompañados de nuestro aderezo especial de la casa.</p>
                    <div class="card-footer">
                        <span class="card-price">₡8,500</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Ensalada Gourmet">Ordenar</a>
                    </div>
                </div>
            </article>

            <!-- 2. Plato Fuerte Especial -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=500&q=80" alt="Plato Fuerte Especial" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Plato Fuerte Especial</h2>
                    <p class="card-desc">Corte premium de la casa acompañado de vegetales salteados y puré artesanal.</p>
                    <div class="card-footer">
                        <span class="card-price">₡14,000</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Plato Fuerte Especial">Ordenar</a>
                    </div>
                </div>
            </article>

            <!-- 3. Fettuccine Alfredo -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=500&q=80" alt="Fettuccine Alfredo" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Fettuccine Alfredo</h2>
                    <p class="card-desc">Pasta fresca bañada en cremosa salsa alfredo artesanal con queso parmesano madurado.</p>
                    <div class="card-footer">
                        <span class="card-price">₡10,500</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Fettuccine Alfredo">Ordenar</a>
                    </div>
                </div>
            </article>

            <!-- 4. Cocteles de Autor -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=500&q=80" alt="Cocteles de Autor" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Cocteles de Autor</h2>
                    <p class="card-desc">Bebida refrescante elaborada con frutas naturales y licores seleccionados.</p>
                    <div class="card-footer">
                        <span class="card-price">₡5,500</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Cocteles de Autor">Ordenar</a>
                    </div>
                </div>
            </article>

            <!-- 5. Burger Bon Appétit -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80" alt="Burger Bon Appétit" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Burger Bon Appétit</h2>
                    <p class="card-desc">200g de carne angus, queso cheddar fundido, tocineta crujiente y papas rústicas.</p>
                    <div class="card-footer">
                        <span class="card-price">₡9,800</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Burger Bon Appétit">Ordenar</a>
                    </div>
                </div>
            </article>

            <!-- 6. Postre Artesanal -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=500&q=80" alt="Postre Artesanal" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Postre Artesanal</h2>
                    <p class="card-desc">Deliciosa combinación dulce de chocolate y frutos rojos para finalizar tu experiencia.</p>
                    <div class="card-footer">
                        <span class="card-price">₡4,500</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Postre Artesanal">Ordenar</a>
                    </div>
                </div>
            </article>

            <!-- 7. Pizza Artesanal -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=500&q=80" alt="Pizza Artesanal" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Pizza Artesanal</h2>
                    <p class="card-desc">Masa delgada al horno de leña con prosciutto, rúcula fresca y queso mozzarella.</p>
                    <div class="card-footer">
                        <span class="card-price">₡11,200</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Pizza Artesanal">Ordenar</a>
                    </div>
                </div>
            </article>

            <!-- 8. Bisque de Mariscos -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=500&q=80" alt="Bisque de Mariscos" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Bisque de Mariscos</h2>
                    <p class="card-desc">Concentrado de mariscos frescos con un toque de crema de leche y especias finas.</p>
                    <div class="card-footer">
                        <span class="card-price">₡12,000</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Bisque de Mariscos">Ordenar</a>
                    </div>
                </div>
            </article>

            <!-- 9. Tacos de Pescado -->
            <article class="card">
                <img src="https://images.unsplash.com/photo-1565299585323-38d6b0865b47?auto=format&fit=crop&w=500&q=80" alt="Tacos de Pescado" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">Tacos de Pescado</h2>
                    <p class="card-desc">Tres tacos en tortilla de maíz artesanal con pescado empanizado y mayonesa de chipotle.</p>
                    <div class="card-footer">
                        <span class="card-price">₡8,900</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar Tacos de Pescado">Ordenar</a>
                    </div>
                </div>
            </article>
        </section>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 Bon Appétit - Todos los derechos reservados.</p>
    </footer>
</body>
</html>
