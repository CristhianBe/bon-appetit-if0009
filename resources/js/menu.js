import { ListadoView } from './views/ListadoView.js';
import { ApiClient } from './api/ApiClient.js';
import { conectarBusqueda } from './busqueda.js';
import { manejarExpiracionDeSesion, restaurarFormularioTemporal } from './sesion.js';

manejarExpiracionDeSesion();

document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('app-container');
    const inputBusqueda = document.getElementById('buscar-platillo');

    if (container) {
        const apiClient = new ApiClient();

        try {
            const listadoView = new ListadoView(apiClient, container);
            await listadoView.render(1, '');

            if (inputBusqueda) {
                conectarBusqueda(inputBusqueda, listadoView);
            }
        } catch (error) {
            console.error('Error en la petición API:', error);
            container.innerHTML = `
                <div class="state-container" style="text-align: center; padding: 50px;">
                    <p style="color: #a00; font-size: 1.2rem;">Error al cargar los platillos desde el servidor.</p>
                </div>
            `;
        }
    }

    restaurarFormularioTemporal();
});