export class FormularioView {
    constructor(apiClient, listadoView) {
        this.apiClient = apiClient;
        this.listadoView = listadoView;

        // Elementos del DOM
        this.modal = document.getElementById('modal-formulario');
        this.form = document.getElementById('form-platillo');
        this.titulo = document.getElementById('modal-titulo');
        
        // Inputs
        this.inputId = document.getElementById('platillo-id');
        this.inputNombre = document.getElementById('platillo-nombre');
        this.inputDesc = document.getElementById('platillo-descripcion');
        this.inputPrecio = document.getElementById('platillo-precio');
        this.inputImagen = document.getElementById('platillo-imagen');
        this.inputCategoria = document.getElementById('platillo-categoria'); // Nuevo select

        this.categoriasCargadas = false; // Evita hacer peticiones repetidas

        this.bindEvents();
    }

    // Método para traer las categorías de la BD
    async cargarCategorias() {
        if (this.categoriasCargadas) return;

        try {
            const response = await this.apiClient.get('/categorias');
            const categorias = response.data || response;
            
            let options = '<option value="">Seleccione una categoría...</option>';
            categorias.forEach(cat => {
                options += `<option value="${cat.id}">${cat.nombre}</option>`;
            });
            
            this.inputCategoria.innerHTML = options;
            this.categoriasCargadas = true;
        } catch (error) {
            console.error("Error al cargar categorías:", error);
            this.inputCategoria.innerHTML = '<option value="">Error al cargar</option>';
        }
    }

    bindEvents() {
        // Abrir modal para crear nuevo
        document.getElementById('btn-crear').addEventListener('click', async () => {
            await this.cargarCategorias(); // Nos aseguramos de tener la lista
            
            this.form.reset();
            this.inputId.value = '';
            this.titulo.innerText = 'Nuevo Platillo';
            this.modal.showModal();
        });

        // Cerrar modal
        document.getElementById('btn-cancelar').addEventListener('click', () => {
            this.modal.close();
        });

        // Escuchar cuando el ListadoView pide editar un platillo
        document.addEventListener('editarPlatillo', async (e) => {
            await this.cargarCategorias(); // Nos aseguramos de tener la lista

            const id = e.detail.id;
            const response = await this.apiClient.get(`/productos/${id}`);
            const platillo = response.data || response;

            // Llenamos el formulario
            this.inputId.value = platillo.id;
            this.inputNombre.value = platillo.nombre;
            this.inputDesc.value = platillo.descripcion;
            this.inputPrecio.value = platillo.precio;
            this.inputImagen.value = platillo.imagen || '';
            
            // Asignamos la FK de la categoría. Asumimos que la columna se llama categoria_id
            this.inputCategoria.value = platillo.categoria_id || ''; 
            
            this.titulo.innerText = 'Editar Platillo';
            this.modal.showModal();
        });

        // Enviar formulario (Guardar o Actualizar)
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.save();
        });
    }

    async save() {
        // 1. Limpiar errores de la pantalla anterior
        document.querySelectorAll('[id^="error-"]').forEach(el => el.innerText = '');

        const id = this.inputId.value;
        const payload = {
            nombre: this.inputNombre.value,
            descripcion: this.inputDesc.value,
            precio: this.inputPrecio.value,
            imagen: this.inputImagen.value,
            categoria_id: this.inputCategoria.value
        };

        try {
            if (id) {
                await this.apiClient.put(`/productos/${id}`, payload);
            } else {
                await this.apiClient.post(`/productos`, payload);
            }
            
            this.modal.close();
            // Refresca la vista
            this.listadoView.render(this.listadoView.currentPage, this.listadoView.currentSearch);
            
        } catch (error) {
            // MAGIA DE LA RÚBRICA: Atrapamos el error de validación 422 de Laravel
            if (error.status === 422 && error.data && error.data.errors) {
                const errores = error.data.errors;
                
                // Muestra el mensaje de Laravel bajo el campo que falló
                if (errores.nombre) document.getElementById('error-nombre').innerText = errores.nombre[0];
                if (errores.descripcion) document.getElementById('error-descripcion').innerText = errores.descripcion[0];
                if (errores.precio) document.getElementById('error-precio').innerText = errores.precio[0];
                if (errores.imagen) document.getElementById('error-imagen').innerText = errores.imagen[0];
                if (errores.categoria_id) document.getElementById('error-categoria_id').innerText = errores.categoria_id[0];
                
            } else {
                console.error("Error del backend:", error);
                alert("Ocurrió un error inesperado al guardar el platillo.");
            }
        }
    }
}