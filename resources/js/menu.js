import { ListadoView, colones } from './views/ListadoView.js';
import { avisar } from './panel.js';

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('app-container');
    if (!container) return;

    // Cliente mínimo para la API pública de Laravel (GET /api/productos no requiere token).
    const apiClient = {
        async get(endpoint) {
            const response = await fetch(`/api${endpoint}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(`El servidor respondió ${response.status}.`);
            return response.json();
        },
    };

    const vista = new ListadoView(apiClient, container);

    // Chips de categoría, construidos con las categorías que vienen en los datos.
    const grupo = document.querySelector('[data-menu-categorias]');
    vista.onCategorias = (categorias) => {
        grupo.replaceChildren();
        ['', ...categorias].forEach((nombre, i) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'chip';
            chip.textContent = nombre || 'Todas';
            chip.setAttribute('aria-pressed', String(i === 0));
            chip.addEventListener('click', () => {
                grupo.querySelectorAll('.chip').forEach((c) => c.setAttribute('aria-pressed', String(c === chip)));
                vista.filtrar({ categoria: nombre });
            });
            grupo.append(chip);
        });
    };

    document.querySelector('[data-menu-buscar]')
        ?.addEventListener('input', (e) => vista.filtrar({ texto: e.target.value }));
    document.querySelector('[data-menu-disponibles]')
        ?.addEventListener('change', (e) => vista.filtrar({ soloDisponibles: e.target.checked }));

    // Barra de ticket: suma lo que se va añadiendo (solo en el navegador, demostración).
    const ticket = { cantidad: 0, total: 0 };
    const barra = document.querySelector('[data-ticket]');
    container.addEventListener('click', (e) => {
        const boton = e.target.closest('[data-agregar]');
        if (!boton) return;
        const producto = vista.items.find((p) => String(p.id) === boton.dataset.agregar);
        if (!producto) return;
        ticket.cantidad += 1;
        ticket.total += Number(producto.precio || 0);
        barra.hidden = false;
        barra.querySelector('[data-ticket-cantidad]').textContent = `${ticket.cantidad} ${ticket.cantidad === 1 ? 'producto' : 'productos'}`;
        barra.querySelector('[data-ticket-total]').textContent = colones(ticket.total);
        avisar(`${producto.nombre} añadido al ticket`);
    });

    vista.render(1);
});
