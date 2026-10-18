/**
 * Expiración de sesión (Persona 5).
 *
 * Cuando ApiClient detecta un 401 (token ausente, inválido o expirado), dispara
 * ApiClient.UNAUTHORIZED_EVENT en `window` (ver ApiClient.js). Este módulo escucha
 * ese evento, guarda temporalmente los datos del formulario que el usuario tenía
 * abierto (si había alguno) y redirige al login — para que, al volver a iniciar
 * sesión, pueda recuperar lo que estaba escribiendo sin tener que empezar de cero.
 *
 * No depende de ningún formulario concreto: sirve para el formulario de platillos,
 * el de reservas, o cualquier otro que agregue el equipo más adelante.
 */

import { ApiClient } from './api/ApiClient.js';

const CLAVE_STORAGE = 'bon-appetit:formulario-pendiente';

/**
 * Serializa los campos (name o id) de un formulario a un objeto plano.
 * @param {HTMLFormElement} form
 * @returns {Record<string, string>}
 */
function serializarFormulario(form) {
    const datos = {};

    for (const campo of form.elements) {
        if (!campo.name && !campo.id) continue;
        if (campo.type === 'password' || campo.type === 'submit' || campo.type === 'button') continue;

        const clave = campo.name || campo.id;
        datos[clave] = campo.value;
    }

    return datos;
}

/**
 * Vuelca un objeto plano { campo: valor } sobre los campos de un formulario,
 * buscando cada uno primero por `name` y, si no existe, por `id`.
 * @param {HTMLFormElement} form
 * @param {Record<string, string>} datos
 */
function restaurarEnFormulario(form, datos) {
    for (const [clave, valor] of Object.entries(datos)) {
        const campo = form.elements.namedItem(clave) || form.querySelector(`#${CSS.escape(clave)}`);
        if (campo) campo.value = valor;
    }
}

/**
 * Guarda temporalmente (sessionStorage) los datos del primer formulario visible
 * de la página actual, junto con la ruta a la que pertenece.
 * @param {HTMLFormElement} [form] - Formulario a guardar; si se omite, se busca el primero del documento.
 */
export function guardarFormularioTemporal(form = document.querySelector('form')) {
    if (!form) return;

    sessionStorage.setItem(CLAVE_STORAGE, JSON.stringify({
        ruta: window.location.pathname,
        datos: serializarFormulario(form),
    }));
}

/**
 * Si hay un formulario guardado para la ruta actual, lo restaura y limpia el storage.
 * Se llama al cargar la página a la que el usuario vuelve tras iniciar sesión de nuevo.
 * @param {HTMLFormElement} [form] - Formulario donde restaurar; si se omite, se busca el primero del documento.
 * @returns {boolean} true si se restauró algo.
 */
export function restaurarFormularioTemporal(form = document.querySelector('form')) {
    const guardado = sessionStorage.getItem(CLAVE_STORAGE);
    if (!guardado || !form) return false;

    const { ruta, datos } = JSON.parse(guardado);
    sessionStorage.removeItem(CLAVE_STORAGE);

    if (ruta !== window.location.pathname) return false;

    restaurarEnFormulario(form, datos);
    return true;
}

/**
 * Activa el manejo automático de expiración de sesión: al recibir un 401,
 * guarda el formulario actual y redirige al login, recordando a dónde volver.
 */
export function manejarExpiracionDeSesion() {
    window.addEventListener(ApiClient.UNAUTHORIZED_EVENT, () => {
        guardarFormularioTemporal();

        const volverA = encodeURIComponent(window.location.pathname + window.location.search);
        window.location.href = `/login?redirect=${volverA}`;
    });
}
