/**
 * Comportamiento global del panel web: guarda de sesión, cierre de sesión, filtrado del menú
 * por rol, diálogos y login. Lo específico de cada pantalla vive en resources/js/pages/*.js.
 */
import {
    ETIQUETA_ROL, DURACION_TOKEN_MS, api, aplicarRoles, asegurarRol, borrarSesion, guardarSesion,
    sesionVigente, usuario, avisar,
} from './core.js';

// menu.js y otras páginas importaban avisar desde aquí: se mantiene la exportación.
export { avisar };

function iniciales(nombre = '') {
    return nombre.split(/\s+/).filter(Boolean).slice(0, 2).map((p) => p[0].toUpperCase()).join('') || 'BA';
}

async function protegerPagina() {
    const sesion = sesionVigente();
    if (!sesion) {
        window.location.replace('/login');
        return;
    }

    // Menú lateral y botones se muestran según el rol (administrador / mesero / cajero).
    await asegurarRol();
    const actual = usuario();
    document.querySelectorAll('[data-user-name]').forEach((el) => { el.textContent = actual?.name ?? 'Usuario'; });
    document.querySelectorAll('[data-user-initials]').forEach((el) => { el.textContent = iniciales(actual?.name); });
    document.querySelectorAll('[data-user-role]').forEach((el) => { el.textContent = ETIQUETA_ROL[actual?.rol] ?? 'Sin rol'; });
    aplicarRoles();

    document.querySelectorAll('[data-logout]').forEach((boton) => {
        boton.addEventListener('click', async () => {
            boton.disabled = true;
            try {
                await api.post('/logout', null);
            } catch {
                /* aunque falle la red, la sesión local se cierra igual */
            }
            borrarSesion();
            window.location.replace('/login');
        });
    });
}

/* ----------------------------------------------------------------- Diálogos */
function iniciarDialogos() {
    document.addEventListener('click', (e) => {
        const abrir = e.target.closest('[data-abrir]');
        if (abrir) document.getElementById(abrir.dataset.abrir)?.showModal();

        const cerrar = e.target.closest('[data-cerrar]');
        if (cerrar) cerrar.closest('dialog')?.close();
    });

    // Clic en el fondo oscuro cierra el diálogo (también los que se crean después).
    document.addEventListener('mousedown', (e) => {
        if (e.target instanceof HTMLDialogElement && e.target.open) {
            const caja = e.target.getBoundingClientRect();
            const fuera = e.clientX < caja.left || e.clientX > caja.right || e.clientY < caja.top || e.clientY > caja.bottom;
            if (fuera) e.target.close();
        }
    });
}

/* ----------------------------------------------------------------- Login */
function iniciarLogin() {
    const form = document.querySelector('[data-form-login]');
    if (!form) return;

    if (sesionVigente()) {
        window.location.replace('/mesas');
        return;
    }

    const error = form.querySelector('[data-login-error]');
    if (new URLSearchParams(window.location.search).has('expirada')) {
        error.textContent = 'Tu sesión venció por seguridad. Ingresá de nuevo para continuar.';
        error.hidden = false;
    }
    const boton = form.querySelector('button[type="submit"]');
    const password = form.querySelector('#password');

    form.querySelector('[data-ver-password]').addEventListener('click', (e) => {
        const visible = password.type === 'text';
        password.type = visible ? 'password' : 'text';
        e.currentTarget.setAttribute('aria-pressed', String(!visible));
        e.currentTarget.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    const mostrarError = (texto) => { error.textContent = texto; error.hidden = false; };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        error.hidden = true;
        if (!form.checkValidity()) {
            mostrarError('Ingresá un correo válido y tu contraseña.');
            form.querySelector(':invalid')?.focus();
            return;
        }

        boton.disabled = true;
        boton.textContent = 'Ingresando…';
        try {
            const respuesta = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ email: form.email.value.trim(), password: password.value }),
            });
            const cuerpo = await respuesta.json().catch(() => ({}));

            if (respuesta.ok && cuerpo.token) {
                guardarSesion({ token: cuerpo.token, user: cuerpo.user, expira: Date.now() + DURACION_TOKEN_MS },
                    form.querySelector('[data-recordar]').checked);
                window.location.replace('/mesas');
                return;
            }
            if (respuesta.status === 401) mostrarError('Correo o contraseña incorrectos.');
            else if (respuesta.status === 429) mostrarError('Demasiados intentos. Esperá un minuto e intentá de nuevo.');
            else if (respuesta.status === 422) mostrarError(Object.values(cuerpo.errors ?? {}).flat()[0] ?? 'Revisá los datos ingresados.');
            else mostrarError('No se pudo iniciar sesión. Intentá de nuevo en unos segundos.');
        } catch {
            mostrarError('Sin conexión con el servidor. Revisá tu red e intentá de nuevo.');
        }
        boton.disabled = false;
        boton.textContent = 'Iniciar sesión';
        password.select();
    });
}

/* ----------------------------------------------------------------- Arranque */
document.addEventListener('DOMContentLoaded', () => {
    if (document.body.dataset.page === 'login') {
        iniciarLogin();
        return;
    }
    protegerPagina();
    iniciarDialogos();
});
