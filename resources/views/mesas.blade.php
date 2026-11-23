@use('App\Support\DatosDemo')
@extends('layouts.panel')

@section('title', 'Control de mesas')
@section('heading', 'Control de mesas')
@section('page', 'mesas')

@php
    $total = count($mesas);
    $cuenta = fn ($estado) => collect($mesas)->where('estado', $estado)->count();
    $ocupadas = $cuenta('ocupada');
    $facturacion = collect($mesas)->where('estado', 'ocupada')->sum(fn ($m) => $m['comanda']['total']);
    $zonas = collect($mesas)->pluck('zona')->unique()->values();
@endphp

@section('content')
    <section class="kpis" aria-label="Resumen del salón">
        <article class="panel-card kpi">
            <div class="kpi-head">Libres <span class="kpi-icon"><x-icon name="check" /></span></div>
            <p class="kpi-value kpi-value--success">{{ $cuenta('libre') }}</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Ocupadas <span class="kpi-icon"><x-icon name="cubiertos" /></span></div>
            <p class="kpi-value kpi-value--warning">{{ $ocupadas }}</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Reservadas <span class="kpi-icon"><x-icon name="reservas" /></span></div>
            <p class="kpi-value">{{ $cuenta('reservada') }}</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Ocupación <span class="kpi-icon"><x-icon name="porcentaje" /></span></div>
            <p class="kpi-value">{{ round($ocupadas / max($total, 1) * 100) }}%</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Facturación activa <span class="kpi-icon"><x-icon name="dinero" /></span></div>
            <p class="kpi-value kpi-value--money">{{ DatosDemo::colones($facturacion) }}</p>
        </article>
    </section>

    <div class="toolbar" data-filtros="mesas">
        <div role="group" aria-label="Filtrar por zona" style="display:flex; gap:8px;">
            <button type="button" class="chip" data-filtro="zona" data-valor="" aria-pressed="true">Todas</button>
            @foreach ($zonas as $zona)
                <button type="button" class="chip" data-filtro="zona" data-valor="{{ $zona }}" aria-pressed="false">{{ $zona }}</button>
            @endforeach
        </div>
        <label class="sr-only" for="filtro-estado">Estado de la mesa</label>
        <select id="filtro-estado" class="select" data-filtro-select="estado">
            <option value="">Estado: todos</option>
            <option value="libre">Libres</option>
            <option value="ocupada">Ocupadas</option>
            <option value="reservada">Reservadas</option>
        </select>
        <span class="toolbar-spacer"></span>
        <div class="search">
            <x-icon name="buscar" />
            <label class="sr-only" for="buscar-mesa">Buscar mesa</label>
            <input id="buscar-mesa" type="search" placeholder="Buscar mesa o mesero" data-filtro-buscar>
        </div>
    </div>

    <section class="mesas-grid" aria-label="Mesas del salón" data-lista="mesas">
        @foreach ($mesas as $m)
            @php
                $c = $m['comanda'];
                $retraso = $c && $c['retraso'];
                $badge = match ($m['estado']) {
                    'libre' => ['Libre', 'success'],
                    'ocupada' => $retraso ? ['Retraso', 'danger'] : ['Ocupada', 'warning'],
                    'reservada' => ['Reservada', 'neutral'],
                };
                $buscable = 'mesa '.$m['numero'].' '.$m['zona'].' '.($c['mesero'] ?? '').' '.($m['reserva']['nombre'] ?? '');
            @endphp
            <article @class(['mesa', 'mesa--'.$m['estado'], 'mesa--retraso' => $retraso])
                     data-item data-zona="{{ $m['zona'] }}" data-estado="{{ $m['estado'] }}" data-texto="{{ mb_strtolower($buscable) }}">
                <div class="mesa-head">
                    <h2 class="mesa-num"><span class="sr-only">Mesa </span>{{ $m['numero'] }}</h2>
                    <span class="badge badge--{{ $badge[1] }}">{{ $badge[0] }}</span>
                </div>
                <div class="mesa-meta">
                    <span><x-icon name="personas" /> {{ $m['capacidad'] }} pax</span>
                    <span><x-icon name="ubicacion" /> {{ $m['zona'] }}</span>
                </div>

                @if ($m['estado'] === 'ocupada')
                    <span class="mesa-time"><x-icon name="reloj" /> {{ $c['minutos'] }} min</span>
                    <p class="mesa-amount">{{ DatosDemo::colones($c['total']) }}</p>
                    <p class="muted" style="font-size:12.5px;">Mesero: {{ $c['mesero'] }}</p>
                    <div class="mesa-actions">
                        <a class="btn btn--primary" href="{{ route('pedidos') }}#{{ $c['codigo'] }}">Ver comanda</a>
                        <button type="button" class="btn btn--outline" data-abrir="mesa-{{ $m['numero'] }}">Detalle</button>
                    </div>
                @elseif ($m['estado'] === 'reservada')
                    <p class="mesa-reserva"><strong>{{ $m['reserva']['nombre'] }}</strong>
                        <span class="mesa-meta"><span><x-icon name="reloj" /> Hora: {{ $m['reserva']['hora'] }}</span></span></p>
                    <div class="mesa-actions">
                        <button type="button" class="btn btn--outline" data-demo="Mesa {{ $m['numero'] }} habilitada para {{ $m['reserva']['nombre'] }} (demostración)">Habilitar mesa</button>
                        <button type="button" class="btn btn--ghost" data-abrir="mesa-{{ $m['numero'] }}">Detalle</button>
                    </div>
                @else
                    <p class="mesa-status">Disponible</p>
                    <div class="mesa-actions">
                        <button type="button" class="btn btn--primary" data-demo="Comanda abierta para la mesa {{ $m['numero'] }} (demostración)">Abrir comanda</button>
                        <button type="button" class="btn btn--ghost" data-abrir="mesa-{{ $m['numero'] }}">Detalle</button>
                    </div>
                @endif
            </article>
        @endforeach
    </section>
    <p class="empty-state" data-vacio hidden>No hay mesas que coincidan con los filtros.</p>

    @foreach ($mesas as $m)
        @php $c = $m['comanda']; @endphp
        <dialog class="modal" id="mesa-{{ $m['numero'] }}" aria-labelledby="mesa-{{ $m['numero'] }}-titulo">
            <div class="modal-head">
                <div>
                    <h2 id="mesa-{{ $m['numero'] }}-titulo">Mesa {{ $m['numero'] }}</h2>
                    <p class="muted">{{ $m['zona'] }} · capacidad {{ $m['capacidad'] }} personas</p>
                </div>
                <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar"><x-icon name="cerrar" /></button>
            </div>
            <dl class="modal-body">
                <div class="modal-row"><dt>Estado</dt><dd>{{ ucfirst($m['estado']) }}</dd></div>
                @if ($c)
                    <div class="modal-row"><dt>Comanda</dt><dd>#{{ $c['codigo'] }}</dd></div>
                    <div class="modal-row"><dt>Personas</dt><dd>{{ $c['personas'] }}</dd></div>
                    <div class="modal-row"><dt>Mesero</dt><dd>{{ $c['mesero'] }}</dd></div>
                    <div class="modal-row"><dt>Tiempo en mesa</dt><dd>{{ $c['minutos'] }} min</dd></div>
                    <div class="modal-row"><dt>Productos</dt><dd>{{ $c['cantidad_items'] }}</dd></div>
                    <div class="modal-row"><dt>Total actual</dt><dd>{{ DatosDemo::colones($c['total']) }}</dd></div>
                @elseif ($m['reserva'])
                    <div class="modal-row"><dt>Reserva a nombre de</dt><dd>{{ $m['reserva']['nombre'] }}</dd></div>
                    <div class="modal-row"><dt>Hora</dt><dd>{{ $m['reserva']['hora'] }}</dd></div>
                    <div class="modal-row"><dt>Personas</dt><dd>{{ $m['reserva']['personas'] }}</dd></div>
                @else
                    <div class="modal-row"><dt>Comanda</dt><dd>Sin orden activa</dd></div>
                @endif
            </dl>
            <div class="modal-foot">
                @if ($c)
                    <a class="btn btn--primary" href="{{ route('pedidos') }}#{{ $c['codigo'] }}">Ir a la comanda</a>
                @endif
                <button type="button" class="btn btn--ghost" data-cerrar>Cerrar</button>
            </div>
        </dialog>
    @endforeach
@endsection
