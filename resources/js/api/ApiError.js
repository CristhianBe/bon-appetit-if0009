export class ApiError extends Error {
    constructor(message, { status = null, codigo = null, errors = null } = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.codigo = codigo;
        this.errors = errors;
    }

    isNetworkError() {
        return this.status === null;
    }

    isValidationError() {
        return this.status === 422;
    }

    isUnauthorized() {
        return this.status === 401;
    }

    isForbidden() {
        return this.status === 403;
    }

    isNotFound() {
        return this.status === 404;
    }

    isRateLimited() {
        return this.status === 429;
    }

    isServerError() {
        return this.status !== null && this.status >= 500;
    }

    fieldError(field) {
        return this.errors?.[field]?.[0] ?? null;
    }
}
