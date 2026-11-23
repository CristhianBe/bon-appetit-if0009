@use('App\Support\DatosDemo')
@extends('layouts.panel')

@section('title', 'Comandas activas')
@section('heading', 'Comandas activas')
@section('page', 'comandas')

@php
    $lista = collect($comandas);
    $atrasadas = $lista->where('retraso', true);
    $cuenta = fn ($estado) => $lista->where('estado', $estado)->count();
    $tabs = [
        ['valor' => '', 'texto' => 'Todas', 'n' => $lista->count()],
        ['valor' => 'abierta', 'texto' => 'Abiertas', 'n' => $cuenta('abierta')],
        ['valor' => 'en_preparacion', 'texto' => 'En preparación', 'n' => $cuenta('en_preparacion')],
        ['valor' => 'servida', 'texto' => 'Servidas', 'n' => $cuenta('servida')],
    ];
@endphp

@section('content')
    <section class="kpis" aria-label="Resumen de comandas">
        <article class="panel-card kpi">
            <div class="kpi-head">Comandas activas <span class="kpi-icon"><x-icon name="comandas" /></span></div>
            <p class="kpi-value">{{ $lista->count() }}</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Facturación en curso <span class="kpi-icon"><x-icon name="dinero" /></span></div>
            <p class="kpi-value kpi-value--money">{{ DatosDemo::colones($lista->sum('total')) }}</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">En preparación <span class="kpi-icon"><x-icon name="cocina" /></span></div>
            <p class="kpi-value kpi-value--warning">{{ $cuenta('en_preparacion') }}</p>
        </article>
        <article class="panel-card kpi">
            <div class="kpi-head">Tiempo promedio en mesa <span class="kpi-icon"><x-icon name="reloj" /></span></div>
            <p class="kpi-value">{{ round($lista->avg('minutos')) }} min</p>
        </article>
    </section>

    @if ($atrasadas->isNotEmpty())
        <div class="alert" role="alert">
            <x-icon name="alerta" />
            <span>
                {{ $atrasadas->count() }} {{ $atrasadas->count() === 1 ? 'comanda supera' : 'comandas superan' }}
                los {{ DatosDemo::MINUTOS_RETRASO }} min:
                {{ $atrasadas->map(fn ($c) => 'Mesa '.$c['mesa'].' ('.$c['minutos'].' min)')->join(', ', ' y ') }}.
            </span>
        </div>
    @endif

    <div class="toolbar" data-filtros="comandas">
        <div role="group" aria-label="Filtrar por estado" style="display:flex; gap:8px;">
            @foreach ($tabs as $i => $t)
                <button type="button" class="chip" data-filtro="estado" data-valor="{{ $t['valor'] }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}">
                    {{ $t['texto'] }} <span class="count">({{ $t['n'] }})</span>
                </button>
            @endforeach
        </div>
        <span class="toolbar-spacer"></span>
        <div class="search">
            <x-icon name="buscar" />
            <label class="sr-only" for="buscar-comanda">Buscar comanda o mesa</label>
            <input id="buscar-comanda" type="search" placeholder="# comanda o mesa" data-filtro-buscar>
        </div>
    </div>

    <div class="comandas-layout">
        <section aria-label="Lista de comandas">
            <div class="comanda-list" data-lista="comandas">
                @foreach ($comandas as $i => $c)
                    @php $estado = $c['retraso'] ? ['texto' => 'Retraso', 'tono' => 'danger'] : DatosDemo::estadoComanda($c['estado']); @endphp
                    <button type="button" @class(['comanda-card', 'comanda-card--retraso' => $c['retraso']])
                            data-item data-comanda="{{ $c['codigo'] }}" data-estado="{{ $c['estado'] }}"
                            data-texto="{{ mb_strtolower($c['codigo'].' mesa '.$c['mesa'].' '.$c['mesero']) }}"
                            aria-controls="detalle-{{ $c['codigo'] }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}">
                        <span class="comanda-card-head">
                            <span class="comanda-card-title">Mesa {{ $c['mesa'] }}</span>
                            <span class="badge badge--{{ $estado['tono'] }}">{{ $estado['texto'] }}</span>
                        </span>
                        <span class="comanda-card-sub" style="display:block;">#{{ $c['codigo'] }} · {{ $c['mesero'] }} · {{ $c['cantidad_items'] }} productos</span>
                        <span class="comanda-card-foot">
                            <span class="mesa-time"><x-icon name="reloj" /> {{ $c['minutos'] }} min</span>
                            <span class="comanda-card-total">{{ DatosDemo::colones($c['total']) }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
            <p class="empty-state" data-vacio hidden>No hay comandas con ese filtro.</p>
        </section>

        <div data-detalles>
            @foreach ($comandas as $i => $c)
                @php $estado = DatosDemo::estadoComanda($c['estado']); @endphp
                <article class="panel-card detalle" id="detalle-{{ $c['codigo'] }}" data-detalle="{{ $c['codigo'] }}" @if ($i !== 0) hidden @endif
                         aria-labelledby="detalle-{{ $c['codigo'] }}-titulo" tabindex="-1">
                    <header class="detalle-head">
                        <div>
                            <h2 id="detalle-{{ $c['codigo'] }}-titulo">Mesa {{ $c['mesa'] }}</h2>
                            <p class="muted">Comanda #{{ $c['codigo'] }} · {{ $c['mesero'] }} · {{ $c['personas'] }} pax · {{ $c['minutos'] }} min</p>
                        </div>
                        <span class="badge badge--{{ $estado['tono'] }}">{{ $estado['texto'] }}</span>
                    </header>

                    <ul class="items">
                        @foreach ($c['items'] as $item)
                            <li class="item">
                                <span class="item-qty">{{ $item['cant'] }}×</span>
                                <span>
                                    <span class="item-name">{{ $item['nombre'] }}</span><br>
                                    <span class="muted" style="font-size:12px;">{{ DatosDemo::colones($item['precio']) }} c/u</span>
                                    @isset($item['nota'])
                                        <br><span class="item-note">{{ $item['nota'] }}</span>
                                    @endisset
                                </span>
                                <span class="item-price">{{ DatosDemo::colones($item['cant'] * $item['precio']) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @isset($c['alergia'])
                        <div class="alert alert--warning" style="margin:14px 0 0;">
                            <x-icon name="alerta" /> <span><strong>Alergia:</strong> {{ $c['alergia'] }}</span>
                        </div>
                    @endisset

                    <div class="totales">
                        <div><span>Subtotal</span><span>{{ DatosDemo::colones($c['subtotal']) }}</span></div>
                        <div><span>IVA (13 %)</span><span>{{ DatosDemo::colones($c['iva']) }}</span></div>
                        <div><span>Servicio (10 %)</span><span>{{ DatosDemo::colones($c['servicio']) }}</span></div>
                        <div class="total"><span>Total</span><strong>{{ DatosDemo::colones($c['total']) }}</strong></div>
                    </div>

                    <div class="detalle-actions">
                        <a class="btn btn--outline" href="{{ route('menu') }}">Agregar productos</a>
                        @if ($c['estado'] === 'servida')
                            <button type="button" class="btn btn--outline" disabled>Ya servida</button>
                        @else
                            <button type="button" class="btn btn--outline" data-demo="Comanda #{{ $c['codigo'] }} enviada a cocina (demostración)">Enviar a cocina</button>
                        @endif
                        <button type="button" class="btn btn--primary" data-demo="Cobro de {{ DatosDemo::colones($c['total']) }} registrado para la mesa {{ $c['mesa'] }} (demostración)">Cobrar y cerrar</button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endsection
