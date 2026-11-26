@extends('layouts.panel')

@section('title', 'Menú y catálogo')
@section('heading', 'Menú y catálogo')
@section('page', 'menu')

@push('scripts')
    @vite('resources/js/menu.js')
@endpush

@section('content')
    <p class="page-intro">Platillos y bebidas cargados en vivo desde la API (<code>GET /api/productos</code>).
        <span data-roles="administrador" hidden>Como administrador podés crear, editar y retirar platillos.</span></p>

    <div class="toolbar">
        <div class="search">
            <x-icon name="buscar" />
            <label class="sr-only" for="buscar-platillo">Buscar platillo</label>
            <input id="buscar-platillo" type="search" placeholder="Buscar platillo, bebida, postre…" data-menu-buscar>
        </div>
        <div role="group" aria-label="Filtrar por categoría" data-menu-categorias style="display:flex; gap:8px;"></div>
        <span class="toolbar-spacer"></span>
        <label class="switch"><input type="checkbox" data-menu-disponibles> Solo disponibles</label>
        <button type="button" class="btn btn--outline" data-roles="administrador" data-abrir-categorias hidden>Categorías</button>
        <button type="button" class="btn btn--primary" data-roles="administrador" data-nuevo-platillo hidden><x-icon name="mas" /> Nuevo platillo</button>
    </div>

    {{-- ListadoView pinta aquí los estados de carga, vacío, error y datos. --}}
    <div id="app-container" aria-live="polite"></div>

    {{-- Crear / editar platillo (solo administrador) --}}
    <dialog class="modal" id="dlg-platillo" aria-labelledby="dlg-platillo-titulo">
        <form method="post" action="#" data-form-platillo novalidate>
            <div class="modal-head">
                <h2 id="dlg-platillo-titulo" data-platillo-titulo>Nuevo platillo</h2>
                <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar"><x-icon name="cerrar" /></button>
            </div>
            <div class="modal-body">
                <div class="field">
                    <label for="plt-nombre">Nombre</label>
                    <input id="plt-nombre" name="nombre" type="text" maxlength="100" required placeholder="Ej.: Casado con pollo">
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="plt-categoria">Categoría</label>
                        <select id="plt-categoria" name="categoria_id" required></select>
                    </div>
                    <div class="field">
                        <label for="plt-precio">Precio (₡)</label>
                        <input id="plt-precio" name="precio" type="number" min="1" max="999999" step="1" inputmode="numeric" required>
                    </div>
                </div>
                <div class="field">
                    <label for="plt-descripcion">Descripción <span class="muted" style="font-weight:400;">(opcional)</span></label>
                    <textarea id="plt-descripcion" name="descripcion" rows="2" maxlength="1000"></textarea>
                </div>
                <div class="field">
                    <label for="plt-imagen">Imagen (URL) <span class="muted" style="font-weight:400;">(opcional)</span></label>
                    <input id="plt-imagen" name="imagen_url" type="url" maxlength="255" placeholder="https://…">
                </div>
                <label class="switch"><input type="checkbox" name="disponible" checked> Disponible para pedir</label>
                <p class="field-error" data-form-error role="alert" hidden></p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn--ghost" data-cerrar>Cancelar</button>
                <button type="submit" class="btn btn--primary">Guardar</button>
            </div>
        </form>
    </dialog>

    {{-- Administrar categorías (solo administrador) --}}
    <dialog class="modal" id="dlg-categorias" aria-labelledby="dlg-categorias-titulo">
        <div class="modal-head">
            <div>
                <h2 id="dlg-categorias-titulo">Categorías del menú</h2>
                <p class="muted">Una categoría solo se puede eliminar si no tiene platillos.</p>
            </div>
            <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar"><x-icon name="cerrar" /></button>
        </div>
        <div class="modal-body">
            <ul class="cat-list" data-categorias-lista></ul>
            <form class="cat-nueva" method="post" action="#" data-form-categoria novalidate>
                <label class="sr-only" for="cat-nueva-nombre">Nombre de la nueva categoría</label>
                <input id="cat-nueva-nombre" name="nombre" type="text" maxlength="100" required placeholder="Nueva categoría (ej.: Postres)">
                <button type="submit" class="btn btn--primary">Agregar</button>
            </form>
            <p class="field-error" data-form-error role="alert" hidden></p>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn--ghost" data-cerrar>Cerrar</button>
        </div>
    </dialog>
@endsection
