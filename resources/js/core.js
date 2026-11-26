import { ApiClient } from './api/ApiClient.js';

const CLAVE_SESION = 'ba_sesion';
export const DURACION_TOKEN_MS = 30 * 60 * 1000;

function almacenes() {
    return [sessionStorage, localStorage];
}

export function leerSesion() {
    for (const almacen of almacenes()) {
        try {
            const sesion = JSON.parse(almacen.getItem(CLAVE_SESION) || 'null');
            if (sesion) return sesion;
        } catch {
        }
    }
    return null;
}

export function guardarSesion(sesion, recordar) {
    try {
        (recordar ? localStorage : sessionStorage).setItem(CLAVE_SESION, JSON.stringify(sesion));
    } catch {
    }
}

export function borrarSesion() {
    for (const almacen of almacenes()) {
        try { almacen.removeItem(CLAVE_SESION); } catch { }
    }
}

export function sesionVigente() {
    const sesion = leerSesion();
    if (!sesion?.token || Date.now() > sesion.expira) {
        borrarSesion();
        return null;
    }
    return sesion;
}

function actualizarUsuarioGuardado(user) {
    for (const almacen of almacenes()) {
        try {
            const sesion = JSON.parse(almacen.getItem(CLAVE_SESION) || 'null');
            if (sesion) almacen.setItem(CLAVE_SESION, JSON.stringify({ ...sesion, user }));
        } catch { }
    }
}

export const usuario = () => sesionVigente()?.user ?? null;
export const rol = () => usuario()?.rol ?? null;
export const tieneRol = (...roles) => roles.includes(rol());

export const ETIQUETA_ROL = { administrador: 'Administrador', mesero: 'Mesero', cajero: 'Cajero' };

export const api = new ApiClient({
    baseUrl: '/api',
    getToken: () => sesionVigente()?.token ?? null,
});

window.addEventListener(ApiClient.UNAUTHORIZED_EVENT, () => {
    borrarSesion();
    window.location.replace('/login?expirada=1');
});

let promesaRol = null;

export function asegurarRol() {
    promesaRol ??= (async () => {
        const sesion = sesionVigente();
        if (sesion && !sesion.user?.rol) {
            try {
                const user = await api.get('/user');
                actualizarUsuarioGuardado({ ...sesion.user, ...user });
            } catch { }
        }
        return usuario();
    })();
    return promesaRol;
}

export const escapar = (valor) => String(valor ?? '').replace(/[&<>"']/g, (c) => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
));

export const colones = (monto) => `₡${Math.round(Number(monto || 0)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;

export function minutosDesde(iso) {
    if (!iso) return 0;
    return Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 60000));
}

export function formatoDuracion(minutos) {
    if (minutos < 60) return `${minutos} min`;
    return `${Math.floor(minutos / 60)} h ${String(minutos % 60).padStart(2, '0')} min`;
}

export function fechaIso(fecha = new Date()) {
    return new Date(fecha.getTime() - fecha.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

export function etiquetaFecha(iso) {
    const hoy = new Date();
    const manana = new Date(hoy.getTime() + 86400000);
    if (iso === fechaIso(hoy)) return 'Hoy';
    if (iso === fechaIso(manana)) return 'Mañana';
    const [, mes, dia] = iso.split('-');
    return `${dia}/${mes}`;
}

export function mensajeError(error) {
    if (error?.isNetworkError?.()) return 'Sin conexión con el servidor. Revisá tu red e intentá de nuevo.';
    if (error?.isForbidden?.()) return 'Tu rol no tiene permiso para hacer esto.';
    if (error?.isRateLimited?.()) return 'Demasiados intentos seguidos. Esperá un momento.';
    if (error?.isValidationError?.() && error.errors) {
        const primero = Object.values(error.errors).flat()[0];
        if (primero) return primero;
    }
    if (error?.isServerError?.()) return 'El servidor tuvo un problema. Intentá de nuevo en unos segundos.';
    return error?.message || 'No se pudo completar la acción.';
}

export async function traerTodo(ruta) {
    const separador = ruta.includes('?') ? '&' : '?';
    const primera = await api.get(`${ruta}${separador}page=1`);
    let datos = primera.data ?? [];
    const ultima = primera.meta?.last_page ?? 1;
    for (let pagina = 2; pagina <= ultima; pagina += 1) {
        const siguiente = await api.get(`${ruta}${separador}page=${pagina}`);
        datos = datos.concat(siguiente.data ?? []);
    }
    return datos;
}

let temporizadorAviso;
export function avisar(texto) {
    const toast = document.querySelector('[data-toast]');
    if (!toast) return;
    toast.querySelector('[data-toast-text]').textContent = texto;
    toast.classList.add('is-visible');
    clearTimeout(temporizadorAviso);
    temporizadorAviso = setTimeout(() => toast.classList.remove('is-visible'), 3600);
}

export function confirmar({ titulo, mensaje, aceptar = 'Confirmar', peligro = false }) {
    const dialogo = document.getElementById('dlg-confirmar');
    if (!dialogo) return Promise.resolve(window.confirm(`${titulo}\n${mensaje}`));

    dialogo.querySelector('[data-confirmar-titulo]').textContent = titulo;
    dialogo.querySelector('[data-confirmar-mensaje]').textContent = mensaje;
    const boton = dialogo.querySelector('[data-confirmar-ok]');
    boton.textContent = aceptar;
    boton.classList.toggle('btn--danger', peligro);
    boton.classList.toggle('btn--primary', !peligro);

    return new Promise((resolver) => {
        const cerrar = (valor) => {
            boton.removeEventListener('click', alAceptar);
            dialogo.removeEventListener('close', alCerrar);
            resolver(valor);
        };
        const alAceptar = () => { dialogo.dataset.resultado = 'si'; dialogo.close(); };
        const alCerrar = () => { const si = dialogo.dataset.resultado === 'si'; dialogo.dataset.resultado = ''; cerrar(si); };
        boton.addEventListener('click', alAceptar);
        dialogo.addEventListener('close', alCerrar);
        dialogo.showModal();
    });
}

export function mostrarErrorForm(contenedor, texto) {
    const caja = contenedor.querySelector('[data-form-error]');
    if (!caja) { avisar(texto); return; }
    caja.textContent = texto;
    caja.hidden = !texto;
}

export async function conBoton(boton, accion) {
    const textoOriginal = boton?.textContent;
    if (boton) { boton.disabled = true; boton.setAttribute('aria-busy', 'true'); }
    try {
        return await accion();
    } finally {
        if (boton) {
            boton.disabled = false;
            boton.removeAttribute('aria-busy');
            if (textoOriginal !== undefined) boton.textContent = textoOriginal;
        }
    }
}

export function pintarCargando(contenedor, texto = 'Cargando…') {
    contenedor.innerHTML = `<div class="state-container" role="status"><div class="spinner"></div><p>${escapar(texto)}</p></div>`;
}

export function pintarError(contenedor, texto, alReintentar) {
    contenedor.innerHTML = `
        <div class="state-container" role="alert">
            <p><strong>No se pudo cargar la información</strong></p>
            <p>${escapar(texto)}</p>
            <button type="button" class="btn btn--outline" data-reintentar style="margin-top:12px;">Reintentar</button>
        </div>`;
    contenedor.querySelector('[data-reintentar]').addEventListener('click', alReintentar);
}

export function aplicarRoles(raiz = document) {
    const actual = rol();
    raiz.querySelectorAll('[data-roles]').forEach((el) => {
        const permitidos = el.dataset.roles.split(',').map((r) => r.trim());
        el.hidden = !permitidos.includes(actual);
    });
}
