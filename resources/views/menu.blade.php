@extends('layouts.app')

@section('content')
<main class="menu-container">
    <header class="menu-header">
        <h1>Menú & Catálogo</h1>
        <p class="subtitle">Carta de platillos y bebidas en tiempo real - Bon Appétit</p>
    </header>

    <!-- Filtros de Categorías (Flexbox) -->
    <nav class="categories-filter" aria-label="Filtro de categorías">
        <button class="filter-btn active">Todas (18)</button>
        <button class="filter-btn">Carnes & Fuertes</button>
        <button class="filter-btn">Entradas</button>
        <button class="filter-btn">Pescados</button>
        <button class="filter-btn">Bebidas</button>
        <button class="filter-btn">Postres</button>
    </nav>

    <!-- Cuadrícula de Menú (CSS Grid) -->
    <section class="menu-grid">
        
        <!-- Platillo 1 -->
        <article class="card-dish">
            <div class="card-image-container">
                <span class="badge badge-available">• Disponible</span>
                <img src="https://images.unsplash.com/photo-1558030006-450675393462?auto=format&fit=crop&w=600&q=80" alt="Ribeye Steak 400g" class="dish-img">
            </div>
            <header>
                <h2>Ribeye Steak 400g</h2>
                <span class="category">Carnes & Fuertes</span>
            </header>
            <p class="description">Corte Angus madurado 28 días a la parrilla con mantequilla de finas hierbas.</p>
            <footer class="card-footer">
                <span class="price">₡11.000</span>
                <button class="btn-add">+ Añadir</button>
            </footer>
        </article>

        <!-- Platillo 2 -->
        <article class="card-dish">
            <div class="card-image-container">
                <span class="badge badge-available">• Disponible</span>
                <img src="https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=600&q=80" alt="Salmón Glaseado al Miso" class="dish-img">
            </div>
            <header>
                <h2>Salmón Glaseado al Miso</h2>
                <span class="category">Pescados & Mariscos</span>
            </header>
            <p class="description">Filete fresco del Pacífico caramelizado en miel y puré de camote rústico.</p>
            <footer class="card-footer">
                <span class="price">₡12.500</span>
                <button class="btn-add">+ Añadir</button>
            </footer>
        </article>

        <!-- Platillo 3 -->
        <article class="card-dish">
            <div class="card-image-container">
                <span class="badge badge-available">• Disponible</span>
                <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80" alt="Hamburguesa Artesanal" class="dish-img">
            </div>
            <header>
                <h2>Hamburguesa Artesanal</h2>
                <span class="category">Platos Fuertes</span>
            </header>
            <p class="description">Carne Angus 200g, queso fundido, tocineta y papas rústicas de la casa.</p>
            <footer class="card-footer">
                <span class="price">₡6.500</span>
                <button class="btn-add">+ Añadir</button>
            </footer>
        </article>

        <!-- Platillo 4 -->
        <article class="card-dish">
            <div class="card-image-container">
                <span class="badge badge-available">• Disponible</span>
                <img src="https://images.unsplash.com/photo-1550304943-4f24f54ddde9?auto=format&fit=crop&w=600&q=80" alt="Ensalada César con Pollo" class="dish-img">
            </div>
            <header>
                <h2>Ensalada César con Pollo</h2>
                <span class="category">Entradas Frescas</span>
            </header>
            <p class="description">Lechuga romana fresca, crutones crujientes y aderezo César aparte.</p>
            <footer class="card-footer">
                <span class="price">₡5.200</span>
                <button class="btn-add">+ Añadir</button>
            </footer>
        </article>

        <!-- Platillo 5 (Agotado) -->
        <article class="card-dish card-out-of-stock">
            <div class="card-image-container">
                <span class="badge badge-unavailable">🚫 Agotado / No disp.</span>
                <img src="https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80" alt="Carpaccio de Res" class="dish-img">
            </div>
            <header>
                <h2>Carpaccio de Res</h2>
                <span class="category">Entradas Frías</span>
            </header>
            <p class="description">Láminas finas de lomo de res con alcaparras y emulsión de limón.</p>
            <footer class="card-footer">
                <span class="price">₡7.000</span>
                <button class="btn-add" disabled>No disponible</button>
            </footer>
        </article>

        <!-- Platillo 6 -->
        <article class="card-dish">
            <div class="card-image-container">
                <span class="badge badge-available">• Disponible</span>
                <img src="https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80" alt="Limonada con Hierbabuena" class="dish-img">
            </div>
            <header>
                <h2>Limonada con Hierbabuena</h2>
                <span class="category">Bebidas Naturales</span>
            </header>
            <p class="description">Jugo de limón natural macerado con hojas frescas de hierbabuena.</p>
            <footer class="card-footer">
                <span class="price">₡2.200</span>
                <button class="btn-add">+ Añadir</button>
            </footer>
        </article>

    </section>
</main>
@endsection