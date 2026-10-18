/**
 * Búsqueda optimizada (Persona 5).
 *
 * Conecta un <input> de búsqueda con un ListadoView:
 *  - Debounce: espera a que el usuario deje de escribir antes de disparar la solicitud.
 *  - Cancelación: si llega un criterio nuevo mientras la solicitud anterior sigue en
 *    curso, esa solicitud anterior se cancela con AbortController (nunca se procesa
 *    una respuesta "vieja" que llegue tarde y pise a la más reciente).
 */

/**
 * @param {(...args: any[]) => void} fn
 * @param {number} delayMs
 * @returns {(...args: any[]) => void}
 */
export function debounce(fn, delayMs = 400) {
    let timeoutId = null;

    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delayMs);
    };
}

/**
 * Conecta un input de búsqueda a un ListadoView.
 *
 * @param {HTMLInputElement} inputElement
 * @param {import('./views/ListadoView.js').ListadoView} listadoView
 * @param {Object} [options]
 * @param {number} [options.delayMs] - Tiempo de debounce.
 */
export function conectarBusqueda(inputElement, listadoView, { delayMs = 400 } = {}) {
    let controladorActual = null;

    const buscar = debounce((criterio) => {
        // Cancela la solicitud anterior (si todavía estaba en curso) antes de lanzar la nueva.
        controladorActual?.abort();
        controladorActual = new AbortController();

        listadoView.render(1, criterio, controladorActual.signal);
    }, delayMs);

    inputElement.addEventListener('input', (event) => {
        buscar(event.target.value.trim());
    });
}
