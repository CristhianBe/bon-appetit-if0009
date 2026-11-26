@extends('layouts.panel')

@section('title', 'Control de mesas')
@section('heading', 'Control de mesas')
@section('page', 'mesas')

@push('scripts')
    @vite('resources/js/pages/mesas.js')
@endpush

@section('content')
    {{-- Todo el contenido se carga desde la API (GET /api/mesas y GET /api/reservas) con resources/js/pages/mesas.js --}}
    <section class="kpis" aria-label="Resumen del salón">
        <article class="panel-card kpi">
            <div class="kpi-head">Libres <span class="kpi-icon"><x-icon name="check" /></span></div>
            <p class="kpi-value kpi-value--success" data-kpi="libres">–</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Ocupadas <span class="kpi-icon"><x-icon name="cubiertos" /></span></div>
            <p class="kpi-value kpi-value--warning" data-kpi="ocupadas">–</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Reservadas hoy <span class="kpi-icon"><x-icon name="reservas" /></span></div>
            <p class="kpi-value" data-kpi="reservadas">–</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Ocupación <span class="kpi-icon"><x-icon name="porcentaje" /></span></div>
            <p class="kpi-value" data-kpi="ocupacion">–</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Facturación activa <span class="kpi-icon"><x-icon name="dinero" /></span></div>
            <p class="kpi-value kpi-value--money" data-kpi="facturacion">–</p>
        </article>
    </section>

    <div class="alert alert--info" role="status" data-aviso-reservas hidden>
        <x-icon name="info" /> <span data-aviso-reservas-texto></span>
    </div>

    <div class="toolbar">
        <div role="group" aria-label="Filtrar por zona" style="display:flex; gap:8px;" data-zonas></div>
        <label class="sr-only" for="filtro-estado">Estado de la mesa</label>
        <select id="filtro-estado" class="select" data-filtro-estado>
            <option value="">Estado: todos</option>
            <option value="libre">Libres</option>
            <option value="ocupada">Ocupadas</option>
            <option value="retraso">Con retraso</option>
            <option value="reservada">Reservadas</option>
        </select>
        <span class="toolbar-spacer"></span>
        <div class="search">
            <x-icon name="buscar" />
            <label class="sr-only" for="buscar-mesa">Buscar mesa</label>
            <input id="buscar-mesa" type="search" placeholder="Buscar mesa, mesero o cliente" data-filtro-buscar>
        </div>
        <button type="button" class="btn btn--outline" data-nueva-mesa data-roles="administrador" hidden>
            <x-icon name="mas" /> Nueva mesa
        </button>
    </div>

    <div id="mesas-contenedor" aria-live="polite"></div>

    {{-- Detalle de una mesa (se rellena por JS) --}}
    <dialog class="modal" id="dlg-mesa" aria-labelledby="dlg-mesa-titulo"></dialog>

    {{-- Abrir comanda --}}
    <dialog class="modal" id="dlg-abrir" aria-labelledby="dlg-abrir-titulo">
        <form method="post" action="#" data-form-abrir novalidate>
            <div class="modal-head">
                <div>
                    <h2 id="dlg-abrir-titulo">Abrir comanda</h2>
                    <p class="muted" data-abrir-sub></p>
                </div>
                <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar"><x-icon name="cerrar" /></button>
            </div>
            <div class="modal-body">
                <div class="field">
                    <label for="abrir-personas">Personas en la mesa</label>
                    <input id="abrir-personas" name="personas" type="number" min="1" max="50" required value="2">
                </div>
                <div class="field">
                    <label for="abrir-notas">Notas <span class="muted" style="font-weight:400;">(opcional)</span></label>
                    <input id="abrir-notas" name="notas" type="text" maxlength="255" placeholder="Alergias, celebración…">
                </div>
                <p class="field-error" data-form-error role="alert" hidden></p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn--ghost" data-cerrar>Cancelar</button>
                <button type="submit" class="btn btn--primary">Abrir comanda</button>
            </div>
        </form>
    </dialog>

    {{-- Crear / editar mesa (solo administrador) --}}
    <dialog class="modal" id="dlg-mesa-form" aria-labelledby="dlg-mesa-form-titulo">
        <form method="post" action="#" data-form-mesa novalidate>
            <div class="modal-head">
                <h2 id="dlg-mesa-form-titulo" data-mesa-form-titulo>Nueva mesa</h2>
                <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar"><x-icon name="cerrar" /></button>
            </div>
            <div class="modal-body">
                <div class="field-row">
                    <div class="field">
                        <label for="mesa-numero">Número</label>
                        <input id="mesa-numero" name="numero" type="number" min="1" required>
                    </div>
                    <div class="field">
                        <label for="mesa-capacidad">Capacidad</label>
                        <input id="mesa-capacidad" name="capacidad" type="number" min="1" max="30" required value="4">
                    </div>
                </div>
                <div class="field">
                    <label for="mesa-zona">Zona</label>
                    <input id="mesa-zona" name="zona" type="text" maxlength="40" required list="lista-zonas" value="Salón">
                    <datalist id="lista-zonas"></datalist>
                </div>
                <p class="field-error" data-form-error role="alert" hidden></p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn--danger-outline" data-mesa-eliminar hidden>Eliminar mesa</button>
                <span class="toolbar-spacer"></span>
                <button type="button" class="btn btn--ghost" data-cerrar>Cancelar</button>
                <button type="submit" class="btn btn--primary">Guardar</button>
            </div>
        </form>
    </dialog>
@endsection
