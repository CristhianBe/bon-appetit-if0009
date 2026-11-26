@extends('layouts.panel')

@section('title', 'Usuarios y roles')
@section('heading', 'Usuarios y roles')
@section('page', 'usuarios')

@push('scripts')
    @vite('resources/js/pages/usuarios.js')
@endpush

@section('content')
    {{-- Solo el administrador puede usar esta pantalla (la API devuelve 403 al resto). --}}
    <div class="alert alert--warning" role="alert" data-sin-permiso hidden>
        <x-icon name="alerta" /> <span>Esta sección es solo para administradores.</span>
    </div>

    <div data-contenido-usuarios hidden>
        <p class="page-intro">Personal con acceso al sistema (<code>/api/usuarios</code>). El rol define qué puede hacer cada persona.</p>

        <section class="roles-resumen" aria-label="Roles del sistema" data-roles-resumen></section>

        <div class="toolbar">
            <div class="search">
                <x-icon name="buscar" />
                <label class="sr-only" for="buscar-usuario">Buscar usuario</label>
                <input id="buscar-usuario" type="search" placeholder="Buscar por nombre o correo" data-buscar-usuario>
            </div>
            <span class="toolbar-spacer"></span>
            <button type="button" class="btn btn--primary" data-nuevo-usuario><x-icon name="mas" /> Nuevo usuario</button>
        </div>

        <div class="panel-card" data-lista-usuarios aria-live="polite"></div>
    </div>

    <dialog class="modal" id="dlg-usuario" aria-labelledby="dlg-usuario-titulo">
        <form method="post" action="#" data-form-usuario novalidate>
            <div class="modal-head">
                <h2 id="dlg-usuario-titulo" data-usuario-titulo>Nuevo usuario</h2>
                <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar"><x-icon name="cerrar" /></button>
            </div>
            <div class="modal-body">
                <div class="field">
                    <label for="usr-nombre">Nombre completo</label>
                    <input id="usr-nombre" name="nombre" type="text" maxlength="255" required autocomplete="off">
                </div>
                <div class="field">
                    <label for="usr-correo">Correo electrónico</label>
                    <input id="usr-correo" name="email" type="email" maxlength="255" required autocomplete="off">
                </div>
                <div class="field">
                    <label for="usr-rol">Rol</label>
                    <select id="usr-rol" name="rol" required></select>
                    <span class="field-hint" data-rol-hint></span>
                </div>
                <div class="field">
                    <label for="usr-password" data-password-etiqueta>Contraseña</label>
                    <input id="usr-password" name="password" type="password" minlength="8" autocomplete="new-password">
                    <span class="field-hint" data-password-hint>Mínimo 8 caracteres.</span>
                </div>
                <p class="field-error" data-form-error role="alert" hidden></p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn--ghost" data-cerrar>Cancelar</button>
                <button type="submit" class="btn btn--primary">Guardar</button>
            </div>
        </form>
    </dialog>
@endsection
