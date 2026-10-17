<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú de Platillos - Bon Appétit</title>
    <style>
        body {
            background-color: #fdf8f5;
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Barra de Navegación */
        .main-header {
            background-color: #5a1217;
            color: white;
            padding: 15px 30px;
        }
        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1100px;
            margin: 0 auto;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .nav-bar ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }
        .nav-bar a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        .nav-bar a[aria-current="page"] {
            text-decoration: underline;
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

        /* Cuadrícula del Menú */
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        /* Tarjeta de Platillo */
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

        /* Contenedor de Estados */
        .state-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 20px;
            text-align: center;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            margin: 20px 0;
        }

        .state-message {
            font-size: 1.2rem;
            color: #5a1217;
            font-weight: 500;
        }

        .main-footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 0.9rem;
            margin-top: 40px;
            border-top: 1px solid #ddd;
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

        <!-- Contenedor dinámico -->
        <div id="app-container"></div>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 Bon Appétit - Todos los derechos reservados.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const container = document.getElementById('app-container');

            if (container) {
                // Estado de carga
                container.innerHTML = `
                    <div class="state-container">
                        <p class="state-message">Cargando información del menú, por favor espere...</p>
                    </div>
                `;
                 await new Promise(resolve => setTimeout(resolve, 1200));

                try {
                   
                    const items = [
                        { nombre: 'Ensalada Gourmet', descripcion: 'Ingredientes frescos de estación acompañados de nuestro aderezo especial de la casa', precio: '8,500', imagen: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80' },
                        { nombre: 'Plato Fuerte Especial', descripcion: 'Corte premium de la casa acompañado de vegetales salteados y puré artesanal', precio: '14,000', imagen: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=500&q=80' },
                        { nombre: 'Fettuccine Alfredo', descripcion: 'Pasta fresca bañada en cremosa salsa alfredo artesanal con queso parmesano madurado', precio: '10,500', imagen: 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=500&q=80' },
                        { nombre: 'Cocteles de Autor', descripcion: 'Bebida refrescante elaborada con frutas naturales y licores seleccionados', precio: '5,500', imagen: 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=500&q=80' },
                        { nombre: 'Burger Bon Appétit', descripcion: '200g de carne angus, queso cheddar fundido, tocineta crujiente y papas rústicas', precio: '9,800', imagen: 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=500&q=80' },
                        { nombre: 'Postre Artesanal', descripcion: 'Deliciosa combinación dulce de chocolate y frutos rojos para finalizar tu experiencia.', precio: '4,500', imagen: 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=500&q=80' },
                        { nombre: 'Pizza Artesanal', descripcion: 'Masa delgada al horno de leña con prosciutto, rúcula fresca y queso mozzarella.', precio: '11,200', imagen: 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=500&q=80' },
                        { nombre: 'Bisque de Mariscos', descripcion: 'Concentrado de mariscos frescos con un toque de crema de leche y especias finas.', precio: '12,000', imagen: 'https://images.unsplash.com/photo-1547592166-23ac45744acd?auto=format&fit=crop&w=500&q=80' },
                        { nombre: 'Tacos de Pescado', descripcion: 'Tres tacos en tortilla de maíz artesanal con pescado empanizado y mayonesa de chipotle.', precio: '8,900', imagen: 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?auto=format&fit=crop&w=500&q=80' }
                    ];

                    // Estado vació 
                    if (!items || items.length === 0) {
                        container.innerHTML = `
                            <div class="state-container">
                                <p class="state-message">No se encontraron platillos disponibles en este momento.</p>
                            </div>
                        `;
                        return;
                    }

                    // Estado con datos 
                    let html = '<section class="catalog-grid" aria-label="Catálogo de platillos disponibles">';
                    items.forEach(item => {
                        html += `
                            <article class="card">
                                <img src="${item.imagen}" alt="${item.nombre}" class="card-img">
                                <div class="card-body">
                                    <h2 class="card-title">${item.nombre}</h2>
                                    <p class="card-desc">${item.descripcion}</p>
                                    <div class="card-footer">
                                        <span class="card-price">₡${item.precio}</span>
                                        <a href="/pedidos" class="btn-card" aria-label="Ordenar ${item.nombre}">Ordenar</a>
                                    </div>
                                </div>
                            </article>
                        `;
                    });
                    html += '</section>';
                    
                    container.innerHTML = html;

                } catch (error) {
                    // Estado de error y reintento 
                    container.innerHTML = `
                        <div class="state-container">
                            <p class="state-message" style="color: #a00; margin-bottom: 15px;">Error al conectar con el servidor. No se pudo cargar el menú.</p>
                            <button id="retry-btn" class="btn-card">Reintentar conexión</button>
                        </div>
                    `;
                    
                    setTimeout(() => {
                        const retryBtn = document.getElementById('retry-btn');
                        if (retryBtn) {
                            retryBtn.addEventListener('click', () => location.reload());
                        }
                    }, 100);
                }
            }
        });
    </script>
</body>
</html>
