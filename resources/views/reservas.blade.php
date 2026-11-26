@extends('layouts.panel')

@section('title', 'Reservaciones')
@section('heading', 'Reservaciones')
@section('page', 'reservas')

@push('scripts')
    @vite('resources/js/pages/reservas.js')
@endpush

@section('content')
    <div class="reservas-layout">
        <section class="panel-card card-pad" aria-labelledby="nueva-reserva">
            <h2 class="section-title" id="nueva-reserva">Nueva reserva</h2>

            <div data-roles="cajero" hidden>
                <p class="section-sub">Tu rol (cajero) puede consultar las reservaciones, pero no registrarlas ni cambiarlas.</p>
            </div>

            <div data-roles="administrador,mesero" hidden>
                <p class="section-sub">Registrá los datos del cliente para apartar una mesa.</p>

                <form method="post" action="#" data-form-reserva novalidate>
                    <div class="field">
                        <label for="nombre">Nombre completo</label>
                        <input id="nombre" name="nombre" type="text" autocomplete="off" required maxlength="100" placeholder="Ej.: Juan Pérez López">
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="correo">Correo electrónico <span class="muted" style="font-weight:400;">(opcional)</span></label>
                            <input id="correo" name="correo" type="email" autocomplete="off" maxlength="120" placeholder="nombre@correo.com">
                        </div>
                        <div class="field">
                            <label for="telefono">Teléfono</label>
                            <input id="telefono" name="telefono" type="tel" autocomplete="off" inputmode="tel" maxlength="30" placeholder="8888-8888">
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
                            <input id="personas" name="personas" type="number" min="1" max="30" required value="2">
                        </div>
                        <div class="field">
                            <label for="zona">Zona preferida</label>
                            <select id="zona" name="zona"><option value="">Cualquiera</option></select>
                        </div>
                    </div>
                    <div class="field">
                        <label for="mesa">Mesa</label>
                        <select id="mesa" name="mesa_id"><option value="">Sin asignar todavía</option></select>
                        <span class="field-hint" data-mesa-hint>Solo se listan mesas con capacidad suficiente.</span>
                    </div>
                    <div class="field">
                        <label for="notas">Notas <span class="muted" style="font-weight:400;">(opcional)</span></label>
                        <textarea id="notas" name="notas" rows="2" maxlength="255" placeholder="Cumpleaños, alergias, silla para bebé…"></textarea>
                    </div>
                    <p class="field-error" data-form-error role="alert" hidden></p>
                    <button type="submit" class="btn btn--primary btn--block">Confirmar reserva</button>
                </form>
            </div>
        </section>

        <section class="panel-card card-pad" aria-labelledby="proximas">
            <h2 class="section-title" id="proximas">Próximas reservas</h2>
            <p class="section-sub">Desde hoy en adelante, ordenadas por fecha y hora.</p>
            <div class="toolbar" style="margin-bottom:10px;">
                <div role="group" aria-label="Filtrar por fecha" style="display:flex; gap:8px;" data-grupo-rango>
                    <button type="button" class="chip" data-rango="todas" aria-pressed="true">Todas</button>
                    <button type="button" class="chip" data-rango="hoy" aria-pressed="false">Hoy</button>
                    <button type="button" class="chip" data-rango="manana" aria-pressed="false">Mañana</button>
                </div>
                <span class="toolbar-spacer"></span>
                <label class="switch"><input type="checkbox" data-ver-cerradas> Ver canceladas y cumplidas</label>
            </div>
            <div data-lista-reservas aria-live="polite"></div>
        </section>
    </div>
@endsection
