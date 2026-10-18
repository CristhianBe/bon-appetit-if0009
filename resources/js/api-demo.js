import { ApiClient } from './api/ApiClient.js';
import { ApiError } from './api/ApiError.js';

// Cliente "normal": sin token todavía (nadie ha iniciado sesión en esta página de prueba).
const api = new ApiClient();

// Cliente aparte, con un token inválido a propósito, solo para el panel 3
// (demostrar el 401 y el evento de aviso). No se usa localStorage para no
// pisar una sesión real que el navegador pueda tener guardada.
const apiConTokenInvalido = new ApiClient({ getToken: () => 'token-que-no-existe' });

function panel(id) {
    return document.getElementById(id);
}

function renderOk(el, data) {
    el.innerHTML = `
        <p class="demo-status demo-status--ok">✅ Éxito</p>
        <pre>${JSON.stringify(data, null, 2)}</pre>
    `;
}

function renderError(el, error) {
    if (error instanceof ApiError) {
        const camposHtml = error.errors
            ? `<ul>${Object.entries(error.errors)
                  .map(([campo, mensajes]) => `<li><strong>${campo}:</strong> ${mensajes[0]}</li>`)
                  .join('')}</ul>`
            : '';

        el.innerHTML = `
            <p class="demo-status demo-status--error">❌ ApiError</p>
            <p><strong>status:</strong> ${error.status ?? '(sin respuesta del servidor)'}</p>
            <p><strong>codigo:</strong> ${error.codigo ?? '(ninguno)'}</p>
            <p><strong>message:</strong> ${error.message}</p>
            ${camposHtml}
        `;
    } else if (error.name === 'AbortError') {
        el.innerHTML = `<p class="demo-status demo-status--cancel">🚫 Solicitud cancelada (AbortSignal)</p>`;
    } else {
        el.innerHTML = `<p class="demo-status demo-status--error">❌ Error inesperado: ${error.message}</p>`;
    }
}

// Panel 1: ejemplo EXITOSO — login con las credenciales demo del seeder.
async function demoExito() {
    const el = panel('demo-exito');
    try {
        const data = await api.post('/login', {
            email: 'mesero@bonappetit.test',
            password: 'Password123!',
        });
        renderOk(el, data);
    } catch (error) {
        renderError(el, error);
    }
}

// Panel 2: ejemplo de ERROR — login con datos inválidos, dispara un 422
// con errores de validación por campo (ApiError.errors).
async function demoError() {
    const el = panel('demo-error');
    try {
        const data = await api.post('/login', {
            email: 'no-es-un-correo',
            password: '',
        });
        renderOk(el, data);
    } catch (error) {
        renderError(el, error);
    }
}

// Panel 3: cancelación de solicitudes con AbortController.
async function demoCancelacion() {
    const el = panel('demo-cancelacion');
    const controller = new AbortController();

    el.innerHTML = '<p class="demo-status">⏳ Solicitud en curso... cancelando en 50ms</p>';
    setTimeout(() => controller.abort(), 50);

    try {
        const data = await api.get('/productos', { signal: controller.signal });
        renderOk(el, data);
    } catch (error) {
        renderError(el, error);
    }
}

// Panel 4: 401 real — se usa un token inválido a propósito contra una ruta
// protegida. ApiClient dispara ApiClient.UNAUTHORIZED_EVENT; este panel
// solo escucha ese evento para probar que "avisa a la app".
window.addEventListener(ApiClient.UNAUTHORIZED_EVENT, (event) => {
    const el = panel('demo-401-evento');
    el.innerHTML = `
        <p class="demo-status demo-status--cancel">🔔 Evento recibido: ${ApiClient.UNAUTHORIZED_EVENT}</p>
        <p>La app ya sabe que debe mandar al usuario al login (sin perder lo que llevaba escrito en el formulario).</p>
    `;
});

async function demo401() {
    const el = panel('demo-401');
    try {
        const data = await apiConTokenInvalido.get('/user');
        renderOk(el, data);
    } catch (error) {
        renderError(el, error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    demoExito();
    demoError();
    demoCancelacion();
    demo401();
});
