/**
 * Módulo encargado del renderizado del catálogo de platillos,
 * implementando los 4 estados de la interfaz: cargando, vacío, error y datos.
 * Si el usuario es administrador (opción puedeEditar) cada tarjeta muestra las acciones de
 * gestión del menú: editar, cambiar disponibilidad y eliminar (las atiende menu.js).
 *
 * Los filtros (búsqueda, categoría y "solo disponibles") se aplican en el navegador
 * sobre la página cargada. Todo texto que viene de la API se escapa antes de
 * insertarlo en el HTML, para que un nombre o descripción con etiquetas no se ejecute.
 */

const ICONO_CUBIERTOS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 2v20M4 2v6a3 3 0 0 0 6 0V2"/><path d="M17 22V2c-2.2 1.2-3.5 3.6-3.5 7v4H17"/></svg>';

const escapar = (valor) => String(valor ?? '').replace(/[&<>"']/g, (c) => (
    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
));

// Formato de colones del panel: ₡38.500
export const colones = (monto) => `₡${Math.round(Number(monto || 0)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;

export class ListadoView {
    /**
     * @param {Object} apiClient - Cliente HTTP con un método get(endpoint)
     * @param {HTMLElement} containerElement - Contenedor donde se pinta la vista
     * @param {Object} store - Estado global opcional (setState)
     * @param {{puedeEditar?: boolean}} opciones
     */
    constructor(apiClient, containerElement, store = null, { puedeEditar = false } = {}) {
        this.puedeEditar = puedeEditar;
        this.apiClient = apiClient;
        this.container = containerElement;
        this.store = store;
        this.currentPage = 1;
        this.totalPages = 1;
        this.items = [];
        this.filtros = { texto: '', categoria: '', soloDisponibles: false };
        this.onCategorias = null; // callback para que la página pinte los chips de categoría
    }

    async render(page = 1) {
        this.currentPage = page;
        this.showLoadingState();

        try {
            const response = await this.apiClient.get(`/productos?page=${this.currentPage}&por_pagina=48`);
            this.items = response.data || response.items || [];
            this.totalPages = response.meta?.last_page || response.last_page || 1;

            this.store?.setState?.({ items: this.items, page: this.currentPage });
            this.onCategorias?.(this.categorias());

            if (this.items.length === 0) {
                this.showEmptyState(this.puedeEditar ? 'Todavía no hay platillos. Agregá el primero con «Nuevo platillo».' : 'Todavía no hay platillos registrados en el menú.');
                return;
            }
            this.showDataState();
        } catch (error) {
            console.error('Error al cargar la lista:', error);
            this.showErrorState(error.message || 'No se pudo conectar con el servidor.');
        }
    }

    categorias() {
        const nombres = this.items.map((p) => p.categoria?.nombre).filter(Boolean);
        return [...new Set(nombres)];
    }

    filtrar(cambios) {
        Object.assign(this.filtros, cambios);
        if (this.items.length) this.showDataState();
    }

    itemsVisibles() {
        const { texto, categoria, soloDisponibles } = this.filtros;
        const t = texto.trim().toLowerCase();
        return this.items.filter((p) => (!t || `${p.nombre} ${p.descripcion ?? ''}`.toLowerCase().includes(t))
            && (!categoria || p.categoria?.nombre === categoria)
            && (!soloDisponibles || p.disponible));
    }

    showLoadingState() {
        this.container.innerHTML = `
            <div class="state-container" role="status">
                <div class="spinner"></div>
                <p>Cargando el menú…</p>
            </div>`;
    }

    showEmptyState(mensaje) {
        this.container.innerHTML = `
            <div class="state-container">
                <p><strong>No hay resultados</strong></p>
                <p>${escapar(mensaje)}</p>
            </div>`;
    }

    showErrorState(errorMessage) {
        this.container.innerHTML = `
            <div class="state-container" role="alert">
                <p><strong>No se pudo cargar el menú</strong></p>
                <p>${escapar(errorMessage)}</p>
                <button type="button" class="btn btn--outline" data-reintentar style="margin-top:12px;">Reintentar</button>
            </div>`;
        this.container.querySelector('[data-reintentar]')
            .addEventListener('click', () => this.render(this.currentPage));
    }

    showDataState() {
        const visibles = this.itemsVisibles();
        if (visibles.length === 0) {
            this.showEmptyState('Ningún platillo coincide con los filtros seleccionados.');
            return;
        }

        const tarjetas = visibles.map((p) => {
            const disponible = Boolean(p.disponible);
            const imagen = p.imagen_url
                ? `<img src="${escapar(p.imagen_url)}" alt="" loading="lazy">`
                : ICONO_CUBIERTOS;
            return `
                <article class="dish ${disponible ? '' : 'dish--agotado'}">
                    <div class="dish-media">
                        ${imagen}
                        <span class="badge ${disponible ? 'badge--success' : 'badge--neutral'}">${disponible ? 'Disponible' : 'Agotado'}</span>
                    </div>
                    <div class="dish-body">
                        ${p.categoria?.nombre ? `<span class="dish-cat">${escapar(p.categoria.nombre)}</span>` : ''}
                        <h2 class="dish-name">${escapar(p.nombre)}</h2>
                        ${p.descripcion ? `<p class="dish-desc">${escapar(p.descripcion)}</p>` : ''}
                        <div class="dish-foot">
                            <span class="dish-price">${colones(p.precio)}</span>
                            ${this.puedeEditar ? `
                                <span class="dish-admin">
                                    <button type="button" class="btn btn--outline" data-editar="${escapar(p.id)}">Editar</button>
                                    <button type="button" class="btn btn--ghost" data-alternar="${escapar(p.id)}">${disponible ? 'Marcar agotado' : 'Marcar disponible'}</button>
                                    <button type="button" class="btn btn--ghost btn--peligro" data-eliminar="${escapar(p.id)}" aria-label="Eliminar ${escapar(p.nombre)}">Eliminar</button>
                                </span>` : ''}
                        </div>
                    </div>
                </article>`;
        }).join('');

        const paginacion = this.totalPages > 1 ? `
            <nav class="pagination" aria-label="Páginas del menú">
                <button type="button" class="btn btn--ghost" data-pagina="${this.currentPage - 1}" ${this.currentPage <= 1 ? 'disabled' : ''}>Anterior</button>
                <span>Página <strong>${this.currentPage}</strong> de <strong>${this.totalPages}</strong></span>
                <button type="button" class="btn btn--ghost" data-pagina="${this.currentPage + 1}" ${this.currentPage >= this.totalPages ? 'disabled' : ''}>Siguiente</button>
            </nav>` : '';

        this.container.innerHTML = `<section class="catalog-grid" aria-label="Catálogo de platillos">${tarjetas}</section>${paginacion}`;

        this.container.querySelectorAll('[data-pagina]').forEach((b) => {
            b.addEventListener('click', () => this.render(Number(b.dataset.pagina)));
        });
    }
}
