@extends('layouts.panel')

@section('title', 'Menú y catálogo')
@section('heading', 'Menú y catálogo')
@section('page', 'menu')

@push('scripts')
    @vite('resources/js/menu.js')
@endpush

@section('content')
    <p class="page-intro">Platillos cargados en vivo desde la API (<code>GET /api/productos</code>).</p>

    <div class="toolbar">
        <div class="search">
            <x-icon name="buscar" />
            <label class="sr-only" for="buscar-platillo">Buscar platillo</label>
            <input id="buscar-platillo" type="search" placeholder="Buscar platillo, bebida, postre…" data-menu-buscar>
        </div>
        <div role="group" aria-label="Filtrar por categoría" data-menu-categorias style="display:flex; gap:8px;"></div>
        <span class="toolbar-spacer"></span>
        <label class="switch"><input type="checkbox" data-menu-disponibles> Solo disponibles</label>
    </div>

    {{-- ListadoView pinta aquí los estados de carga, vacío, error y datos. --}}
    <div id="app-container" aria-live="polite"></div>

    <div class="ticket-bar" data-ticket hidden>
        <span><strong data-ticket-cantidad>0 productos</strong> · <span data-ticket-total>₡0</span></span>
        <a class="btn btn--gold" href="{{ route('pedidos') }}">Ver comandas</a>
    </div>
@endsection
