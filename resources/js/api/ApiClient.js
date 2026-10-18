import { ApiError } from './ApiError.js';

/**
 * Nombre del evento que se dispara en `window` cada vez que el backend
 * responde 401 (token ausente, inválido o expirado). El módulo de sesión
 * (Persona 5) se suscribe a este evento para redirigir al login sin que
 * ApiClient tenga que conocer nada de rutas ni del DOM.
 *
 *   window.addEventListener(ApiClient.UNAUTHORIZED_EVENT, (e) => {
 *       // e.detail es el ApiError de la petición que falló
 *       guardarFormularioTemporalmente();
 *       location.href = '/login';
 *   });
 */
const UNAUTHORIZED_EVENT = 'bon-appetit:unauthorized';

/**
 * Cliente HTTP para la API de Bon Appétit.
 *
 * Encapsula:
 *  - la URL base (/api)
 *  - el token de autenticación (Sanctum, cabecera Authorization: Bearer)
 *  - el armado de cabeceras
 *  - el manejo uniforme de errores: cualquier respuesta no-OK se convierte
 *    siempre en un ApiError, nunca en un objeto de fetch crudo.
 *  - la cancelación de solicitudes vía AbortSignal.
 */
export class ApiClient {
    /**
     * @param {Object} [options]
     * @param {string} [options.baseUrl] - Prefijo de todas las rutas, p. ej. "/api".
     * @param {() => (string|null)} [options.getToken] - Función que devuelve el token actual (o null si no hay sesión).
     */
    constructor({ baseUrl = '/api', getToken = () => localStorage.getItem('token') } = {}) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
        this.getToken = getToken;
    }

    /** Arma las cabeceras comunes a toda solicitud, agregando el Bearer token si existe. */
    buildHeaders(extra = {}) {
        const headers = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...extra,
        };

        const token = this.getToken();
        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        return headers;
    }

    /**
     * Solicitud genérica. Los métodos get/post/put/delete de abajo son
     * atajos sobre este método.
     *
     * @param {string} path - Ruta relativa a baseUrl, p. ej. "/productos" o "/comandas/3".
     * @param {Object} [options]
     * @param {string} [options.method]
     * @param {*} [options.body] - Se serializa con JSON.stringify si no es null.
     * @param {AbortSignal} [options.signal] - Para poder cancelar la solicitud (ver AbortController).
     * @param {Object} [options.headers] - Cabeceras extra/override.
     * @returns {Promise<any>} Los datos ya parseados de JSON (o null si la respuesta no trae cuerpo).
     * @throws {ApiError} Si la respuesta no es 2xx, o si falla la conexión.
     */
    async request(path, { method = 'GET', body = null, signal = null, headers = {} } = {}) {
        const url = `${this.baseUrl}${path.startsWith('/') ? path : `/${path}`}`;

        let response;
        try {
            response = await fetch(url, {
                method,
                headers: this.buildHeaders(headers),
                body: body !== null ? JSON.stringify(body) : undefined,
                signal,
            });
        } catch (networkError) {
            // Una cancelación via AbortController también cae acá (name === 'AbortError').
            // Se deja pasar tal cual para que quien llamó pueda distinguir "lo cancelé yo"
            // de "se cayó la red" con un simple `if (error.name === 'AbortError')`.
            if (networkError.name === 'AbortError') {
                throw networkError;
            }
            throw new ApiError('No se pudo conectar con el servidor. Revise su conexión.', {
                status: null,
            });
        }

        // 204 No Content u otras respuestas sin cuerpo.
        let data = null;
        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            data = await response.json().catch(() => null);
        }

        if (!response.ok) {
            const error = new ApiError(data?.message || `Error ${response.status}`, {
                status: response.status,
                codigo: data?.codigo ?? null,
                errors: data?.errors ?? null,
            });

            if (response.status === 401) {
                window.dispatchEvent(new CustomEvent(UNAUTHORIZED_EVENT, { detail: error }));
            }

            throw error;
        }

        return data;
    }

    get(path, options = {}) {
        return this.request(path, { ...options, method: 'GET' });
    }

    post(path, body, options = {}) {
        return this.request(path, { ...options, method: 'POST', body });
    }

    put(path, body, options = {}) {
        return this.request(path, { ...options, method: 'PUT', body });
    }

    patch(path, body, options = {}) {
        return this.request(path, { ...options, method: 'PATCH', body });
    }

    delete(path, options = {}) {
        return this.request(path, { ...options, method: 'DELETE' });
    }
}

ApiClient.UNAUTHORIZED_EVENT = UNAUTHORIZED_EVENT;
