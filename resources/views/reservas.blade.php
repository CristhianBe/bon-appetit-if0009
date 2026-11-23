@extends('layouts.panel')

@section('title', 'Reservaciones')
@section('heading', 'Reservaciones')
@section('page', 'reservas')

@section('content')
    <div class="reservas-layout">
        <section class="panel-card card-pad" aria-labelledby="nueva-reserva">
            <h2 class="section-title" id="nueva-reserva">Nueva reserva</h2>
            <p class="section-sub">Registrá los datos del cliente para apartar una mesa.</p>

            {{-- method="post": los datos personales nunca viajan en la URL. El envío lo maneja panel.js. --}}
            <form method="post" action="#" data-form-reserva novalidate>
                <div class="field">
                    <label for="nombre">Nombre completo</label>
                    <input id="nombre" name="nombre" type="text" autocomplete="name" required placeholder="Ej.: Juan Pérez López">
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="correo">Correo electrónico</label>
                        <input id="correo" name="correo" type="email" autocomplete="email" required placeholder="nombre@correo.com">
                    </div>
                    <div class="field">
                        <label for="telefono">Teléfono</label>
                        <input id="telefono" name="telefono" type="tel" autocomplete="tel" inputmode="tel" placeholder="8888-8888">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="fecha">Fecha</label>
                        <input id="fecha" name="fecha" type="date" required>
                    </div>
                    <div class="field">
                        <label for="hora">Hora</label>
                        <input id="hora" name="hora" type="time" min="11:00" max="22:00" step="900" required value="19:00">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="personas">Número de personas</label>
                        <input id="personas" name="personas" type="number" min="1" max="12" required value="2">
                    </div>
                    <div class="field">
                        <label for="zona">Zona preferida</label>
                        <select id="zona" name="zona">
                            <option>Interior</option>
                            <option>Terraza</option>
                            <option>Barra</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label for="notas">Notas <span class="muted" style="font-weight:400;">(opcional)</span></label>
                    <textarea id="notas" name="notas" rows="2" placeholder="Cumpleaños, alergias, silla para bebé…"></textarea>
                </div>
                <p class="field-error" data-form-error role="alert" hidden></p>
                <button type="submit" class="btn btn--primary btn--block">Confirmar reserva</button>
            </form>
        </section>

        <section class="panel-card card-pad" aria-labelledby="proximas">
            <h2 class="section-title" id="proximas">Próximas reservas</h2>
            <p class="section-sub">Reservas de hoy y mañana.</p>
            <ul class="reserva-list" data-lista-reservas>
                @foreach ($reservas as $r)
                    <li class="reserva">
                        <span class="reserva-hora"><strong>{{ $r['hora'] }}</strong><small>{{ $r['fecha'] }}</small></span>
                        <span class="reserva-info">
                            <strong>{{ $r['nombre'] }}</strong>
                            <span>{{ $r['personas'] }} personas · {{ $r['zona'] }}{{ $r['mesa'] ? ' · Mesa '.$r['mesa'] : '' }}</span>
                        </span>
                        @if ($r['estado'] === 'confirmada')
                            <span class="badge badge--success">Confirmada</span>
                        @else
                            <span class="badge badge--warning">Pendiente</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
@endsection
