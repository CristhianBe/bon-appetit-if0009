/**
 * Error propio del cliente de la API.
 *
 * Envuelve cualquier respuesta no-OK del backend (Laravel) en un objeto con
 * forma conocida, para que el resto de la app (vistas, formularios) no tenga
 * que leer `status`/`data` de fetch directamente en cada sitio.
 *
 * Coincide con el formato que ya arma `bootstrap/app.php` en el backend:
 *   { message: string, codigo: string|null, errors?: { campo: string[] } }
 */
export class ApiError extends Error {
    /**
     * @param {string} message - Mensaje legible para mostrar al usuario.
     * @param {Object} [info]
     * @param {number|null} [info.status] - Código HTTP (401, 404, 422, 500...). null = error de red (sin respuesta del servidor).
     * @param {string|null} [info.codigo] - Código corto que manda el backend (ej. "validacion_fallida", "no_autenticado").
     * @param {Object|null} [info.errors] - Errores de validación por campo: { email: ["..."], password: ["..."] }.
     */
    constructor(message, { status = null, codigo = null, errors = null } = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.codigo = codigo;
        this.errors = errors;
    }

    /** true si el servidor nunca respondió (sin conexión, CORS, timeout, etc.) */
    isNetworkError() {
        return this.status === null;
    }

    /** 422 — errores de validación del formulario */
    isValidationError() {
        return this.status === 422;
    }

    /** 401 — token ausente, inválido o expirado */
    isUnauthorized() {
        return this.status === 401;
    }

    /** 403 — autenticado pero sin permiso sobre el recurso */
    isForbidden() {
        return this.status === 403;
    }

    /** 404 — recurso inexistente */
    isNotFound() {
        return this.status === 404;
    }

    /** 429 — demasiadas solicitudes (throttle) */
    isRateLimited() {
        return this.status === 429;
    }

    /** 5xx — error del servidor */
    isServerError() {
        return this.status !== null && this.status >= 500;
    }

    /**
     * Primer mensaje de error para un campo específico, o null si ese campo
     * no tiene error. Pensado para pintarlo justo debajo del input.
     * @param {string} field
     * @returns {string|null}
     */
    fieldError(field) {
        return this.errors?.[field]?.[0] ?? null;
    }
}
