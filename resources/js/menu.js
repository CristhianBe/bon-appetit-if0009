// Importamos la clase de tus compañeros usando la ruta relativa
import { ListadoView } from './views/ListadoView.js';

document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('app-container');

    if (container) {
        // Cliente para hacer la petición a las rutas de tu Laravel
        const apiClient = {
            async get(endpoint) {
                const response = await fetch(`/api${endpoint}`);
                if (!response.ok) throw new Error('Error al conectar con la base de datos');
                return await response.json();
            }
        };

        try {
            const listadoView = new ListadoView(apiClient, container);
            // Ejecuta el render (los parámetros dependen de cómo lo programó tu equipo)
            await listadoView.render(1, '');
        } catch (error) {
            console.error("Error en la petición API:", error);
            container.innerHTML = `
                <div class="state-container" style="text-align: center; padding: 50px;">
                    <p style="color: #a00; font-size: 1.2rem;">Error al cargar los platillos desde el servidor.</p>
                </div>
            `;
        }
    }
});