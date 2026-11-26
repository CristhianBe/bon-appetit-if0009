import { ApiError } from './ApiError.js';

const UNAUTHORIZED_EVENT = 'bon-appetit:unauthorized';

export class ApiClient {
    constructor({ baseUrl = '/api', getToken = () => localStorage.getItem('token') } = {}) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
        this.getToken = getToken;
    }

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
            if (networkError.name === 'AbortError') {
                throw networkError;
            }
            throw new ApiError('No se pudo conectar con el servidor. Revise su conexión.', {
                status: null,
            });
        }

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
