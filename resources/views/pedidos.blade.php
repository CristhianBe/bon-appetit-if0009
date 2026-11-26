@extends('layouts.panel')

@section('title', 'Comandas activas')
@section('heading', 'Comandas activas')
@section('page', 'comandas')

@push('scripts')
    @vite('resources/js/pages/pedidos.js')
@endpush

@section('content')
    <section class="kpis" aria-label="Resumen de comandas">
        <article class="panel-card kpi">
            <div class="kpi-head">Comandas activas <span class="kpi-icon"><x-icon name="comandas" /></span></div>
            <p class="kpi-value" data-kpi="activas">–</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Facturación en curso <span class="kpi-icon"><x-icon name="dinero" /></span></div>
            <p class="kpi-value kpi-value--money" data-kpi="facturacion">–</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">En preparación <span class="kpi-icon"><x-icon name="cocina" /></span></div>
            <p class="kpi-value kpi-value--warning" data-kpi="preparacion">–</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Tiempo promedio en mesa <span class="kpi-icon"><x-icon name="reloj" /></span></div>
            <p class="kpi-value" data-kpi="promedio">–</p>
        </article>
    </section>

    <div class="alert" role="alert" data-alerta-retraso hidden>
        <x-icon name="alerta" /> <span data-alerta-retraso-texto></span>
    </div>

    <div class="toolbar">
        <div role="group" aria-label="Filtrar por estado" style="display:flex; gap:8px;" data-tabs></div>
        <span class="toolbar-spacer"></span>
        <div class="search">
            <x-icon name="buscar" />
            <label class="sr-only" for="buscar-comanda">Buscar comanda o mesa</label>
            <input id="buscar-comanda" type="search" placeholder="# comanda, mesa o mesero" data-filtro-buscar>
        </div>
    </div>

    <div class="comandas-layout">
        <section aria-label="Lista de comandas">
            <div data-lista aria-live="polite"></div>
        </section>
        <div data-detalle-contenedor></div>
    </div>

    <dialog class="modal modal--wide" id="dlg-productos" aria-labelledby="dlg-productos-titulo">
        <div class="modal-head">
            <div>
                <h2 id="dlg-productos-titulo">Agregar productos</h2>
                <p class="muted" data-productos-sub></p>
            </div>
            <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar"><x-icon name="cerrar" /></button>
        </div>
        <div class="picker-tools">
            <div class="search">
                <x-icon name="buscar" />
                <label class="sr-only" for="buscar-producto">Buscar producto</label>
                <input id="buscar-producto" type="search" placeholder="Buscar platillo o bebida" data-picker-buscar>
            </div>
            <div role="group" aria-label="Categoría" class="picker-chips" data-picker-categorias></div>
            <div class="field picker-nota">
                <label for="picker-nota">Nota para el próximo producto <span class="muted" style="font-weight:400;">(opcional)</span></label>
                <input id="picker-nota" type="text" maxlength="255" placeholder="Sin cebolla, término medio…" data-picker-nota>
            </div>
        </div>
        <div class="picker-list" data-picker-lista aria-live="polite"></div>
        <p class="field-error" style="padding:0 22px;" data-form-error role="alert" hidden></p>
        <div class="modal-foot">
            <span class="muted" data-picker-resumen style="margin-right:auto;"></span>
            <button type="button" class="btn btn--primary" data-cerrar>Listo</button>
        </div>
    </dialog>

    <dialog class="modal" id="dlg-cobrar" aria-labelledby="dlg-cobrar-titulo">
        <form method="post" action="#" data-form-cobrar novalidate>
            <div class="modal-head">
                <div>
                    <h2 id="dlg-cobrar-titulo">Cobrar y cerrar</h2>
                    <p class="muted" data-cobrar-sub></p>
                </div>
                <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar"><x-icon name="cerrar" /></button>
            </div>
            <div class="modal-body">
                <div class="totales" style="margin:0;" data-cobrar-totales></div>
                <fieldset class="metodos" data-cobrar-metodos>
                    <legend>Método de pago</legend>
                </fieldset>
                <div class="field" data-cobrar-efectivo hidden>
                    <label for="cobrar-recibido">Monto recibido <span class="muted" style="font-weight:400;">(opcional, calcula el vuelto)</span></label>
                    <input id="cobrar-recibido" type="number" min="0" step="500" inputmode="numeric" data-cobrar-recibido>
                    <p class="field-hint" data-cobrar-vuelto></p>
                </div>
                <p class="field-error" data-form-error role="alert" hidden></p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn--ghost" data-cerrar>Cancelar</button>
                <button type="submit" class="btn btn--primary">Confirmar cobro</button>
            </div>
        </form>
    </dialog>
@endsection
