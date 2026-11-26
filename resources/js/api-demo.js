import { ApiClient } from './api/ApiClient.js';
import { ApiError } from './api/ApiError.js';

const api = new ApiClient();

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
