import { ListadoView } from './views/ListadoView.js';
import { FormularioView } from './views/FormularioView.js'; // Importamos la nueva clase

document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('app-container');

    if (container) {
        // Cliente API mejorado para el CRUD completo
        const apiClient = {
            async get(endpoint) {
                const res = await fetch(`/api${endpoint}`);
                if (!res.ok) throw new Error('Error GET');
                return await res.json();
            },
            async post(endpoint, data) {
                const res = await fetch(`/api${endpoint}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });
                const responseData = await res.json();
                // Si falla (ej: 422), arrojamos el estado y los datos de Laravel
                if (!res.ok) throw { status: res.status, data: responseData };
                return responseData;
            },
            async put(endpoint, data) {
                const res = await fetch(`/api${endpoint}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });
                const responseData = await res.json();
                if (!res.ok) throw { status: res.status, data: responseData };
                return responseData;
            },
            async delete(endpoint) {
                const res = await fetch(`/api${endpoint}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Error DELETE');
                return true;
            }
        };

        try {
            // Inicializamos ambas vistas y las conectamos
            const listadoView = new ListadoView(apiClient, container);
            const formularioView = new FormularioView(apiClient, listadoView);
            
            await listadoView.render(1, '');
        } catch (error) {
            console.error(error);
        }
    }
});