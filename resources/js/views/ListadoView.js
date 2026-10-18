/**
 * Módulo encargado del renderizado y gestión del listado paginado
 * de la aplicación, implementando los 4 estados de la interfaz
 */

export class ListadoView {
    /**
     * @param {Object} apiClient - Instancia del cliente HTTP/API
     * @param {HTMLElement} containerElement - Contenedor HTML donde se renderiza la vista
     * @param {Object} store - Módulo de estado global de la aplicación
     */
    constructor(apiClient, containerElement, store = null) {
        this.apiClient = apiClient;
        this.container = containerElement;
        this.store = store;
        this.currentPage = 1;
        this.totalPages = 1;
        this.currentSearch = '';
    }

    /**
     * Método principal para cargar y renderizar los datos
     * @param {number} page - Número de página a consultar
     * @param {string} search - Criterio de búsqueda
     */
    async render(page = 1, search = '') {
        this.currentPage = page;
        this.currentSearch = search;

        this.showLoadingState();

        try {

           const endpoint = `/productos?page=${this.currentPage}&search=${encodeURIComponent(this.currentSearch)}`;
            const response = await this.apiClient.get(endpoint);

            const data = response.data || response.items || [];
            this.totalPages = response.last_page || response.totalPages || 1;

            if (this.store && typeof this.store.setState === 'function') {
                this.store.setState({ items: data, page: this.currentPage });
            }

            if (data.length === 0) {
                this.showEmptyState();
                return;
            }

       this.showDataState(data, response);

        } catch (error) {

            console.error('Error al cargar la lista:', error);
            this.showErrorState(error.message || 'No se pudo conectar con el servidor.');
        }
    }

    /**
     * Renderiza el estado de carga
     */
    showLoadingState() {
        this.container.innerHTML = `
            <div class="state-container state-loading" aria-live="polite">
                <div class="spinner"></div>
                <p>Cargando información del menú, por favor espere...</p>
            </div>
        `;
    }

    /**
     * Renderiza el estado vacío
     */
    showEmptyState() {
        this.container.innerHTML = `
            <div class="state-container state-empty">
                <div class="empty-icon">🍽️</div>
                <h3>No se encontraron resultados</h3>
                <p>No hay platillos o registros disponibles para mostrar${this.currentSearch ? ` con el término "${this.currentSearch}"` : ''}.</p>
            </div>
        `;
    }

    /**
     * Renderiza el estado de error y vincula el botón de reintento
     * @param {string} errorMessage 
     */
    showErrorState(errorMessage) {
        this.container.innerHTML = `
            <div class="state-container state-error" role="alert">
                <div class="error-icon">⚠️</div>
                <h3>Error de conexión</h3>
                <p>${errorMessage}</p>
                <button id="btn-retry" class="btn-card">Reintentar conexión</button>
            </div>
        `;


        const retryBtn = this.container.querySelector('#btn-retry');
        if (retryBtn) {
            retryBtn.addEventListener('click', () => {
                this.render(this.currentPage, this.currentSearch);
            });
        }
    }

    /**
     * Renderiza la lista con datos y sus controles de paginación
     * @param {Array} items - Lista de elementos devueltos por la API
     * @param {Object} paginationMeta - Metadata de paginación del backend
     */
    showDataState(items, paginationMeta) {
        const cardsHtml = items.map(item => `
            <article class="card">
                <img src="${item.imagen || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80'}" alt="${item.nombre}" class="card-img">
                <div class="card-body">
                    <h2 class="card-title">${item.nombre}</h2>
                    <p class="card-desc">${item.descripcion || 'Sin descripción'}</p>
                    <div class="card-footer">
                        <span class="card-price">₡${Number(item.precio || 0).toLocaleString()}</span>
                        <a href="/pedidos" class="btn-card" aria-label="Ordenar ${item.nombre}">Ordenar</a>
                    </div>
                </div>
            </article>
        `).join('');

        this.container.innerHTML = `
            <div class="state-container state-data">
                <section class="catalog-grid" aria-label="Catálogo de platillos">
                    ${cardsHtml}
                </section>

                <nav class="pagination-container" aria-label="Navegación de páginas">
                    <button id="btn-prev" class="btn-pagination" ${this.currentPage <= 1 ? 'disabled' : ''}>
                        &laquo; Anterior
                    </button>
                    <span class="page-indicator">Página <strong>${this.currentPage}</strong> de <strong>${this.totalPages}</strong></span>
                    <button id="btn-next" class="btn-pagination" ${this.currentPage >= this.totalPages ? 'disabled' : ''}>
                        Siguiente &raquo;
                    </button>
                </nav>
            </div>
        `;

        this.bindPaginationEvents();
    }

    /**
     * Asigna los escuchadores de eventos para Anterior y Siguiente
     */
    bindPaginationEvents() {
        const prevBtn = this.container.querySelector('#btn-prev');
        const nextBtn = this.container.querySelector('#btn-next');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (this.currentPage > 1) {
                    this.render(this.currentPage - 1, this.currentSearch);
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (this.currentPage < this.totalPages) {
                    this.render(this.currentPage + 1, this.currentSearch);
                }
            });
        }
    }
}