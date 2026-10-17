<main class="menu-container">
    <h1>Nuestra Carta</h1>
    <div id="app-container"></div>
</main>

<!-- Script integrado directamente para renderizar los 4 estados sin requerir npm/Vite -->
<script type="module">
    // Importamos tu clase ListadoView directamente desde la ruta de public o resources
    import { ListadoView } from "{{ asset('js/views/ListadoView.js') }}";

    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.getElementById('app-container');

        if (container) {
            const mockApiClient = {
                async get(endpoint) {
                    const response = await fetch(`/api${endpoint}`);
                    if (!response.ok) throw new Error('Error al conectar con el servidor');
                    return await response.json();
                }
            };

            const listadoView = new ListadoView(mockApiClient, container);
            await listadoView.render(1, '');
        }
    });
</script>