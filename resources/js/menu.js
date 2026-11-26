import { ListadoView } from './views/ListadoView.js';
import {
    api, asegurarRol, avisar, confirmar, conBoton, escapar, mensajeError, mostrarErrorForm, tieneRol, traerTodo,
} from './core.js';

document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('app-container');
    if (!container) return;

    await asegurarRol();
    const admin = tieneRol('administrador');

    const vista = new ListadoView(api, container, null, { puedeEditar: admin });

    const grupo = document.querySelector('[data-menu-categorias]');
    vista.onCategorias = (categorias) => {
        if (vista.filtros.categoria && !categorias.includes(vista.filtros.categoria)) vista.filtros.categoria = '';
        grupo.replaceChildren();
        ['', ...categorias].forEach((nombre) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'chip';
            chip.textContent = nombre || 'Todas';
            chip.setAttribute('aria-pressed', String(nombre === vista.filtros.categoria));
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

    vista.render(1);
    if (!admin) return;

    let categorias = [];
    const cargarCategorias = async () => { categorias = await traerTodo('/categorias?por_pagina=50'); };

    const dlgPlatillo = document.getElementById('dlg-platillo');
    const formPlatillo = dlgPlatillo.querySelector('form');
    let editando = null;

    async function abrirFormulario(producto = null) {
        try { await cargarCategorias(); } catch (error) { avisar(mensajeError(error)); return; }
        if (categorias.length === 0) {
            avisar('Primero creá al menos una categoría con el botón «Categorías».');
            return;
        }
        editando = producto;
        dlgPlatillo.querySelector('[data-platillo-titulo]').textContent = producto ? `Editar «${producto.nombre}»` : 'Nuevo platillo';
        formPlatillo.categoria_id.innerHTML = categorias.map((c) => `<option value="${escapar(c.id)}">${escapar(c.nombre)}</option>`).join('');
        formPlatillo.nombre.value = producto?.nombre ?? '';
        formPlatillo.categoria_id.value = producto?.categoria_id ?? categorias[0].id;
        formPlatillo.precio.value = producto ? Math.round(Number(producto.precio)) : '';
        formPlatillo.descripcion.value = producto?.descripcion ?? '';
        formPlatillo.imagen_url.value = producto?.imagen_url ?? '';
        formPlatillo.disponible.checked = producto ? Boolean(producto.disponible) : true;
        mostrarErrorForm(dlgPlatillo, '');
        dlgPlatillo.showModal();
        formPlatillo.nombre.focus();
    }

    formPlatillo.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!formPlatillo.checkValidity()) {
            mostrarErrorForm(dlgPlatillo, 'Revisá el nombre, la categoría, el precio y la URL de la imagen.');
            formPlatillo.querySelector(':invalid')?.focus();
            return;
        }
        const datos = {
            nombre: formPlatillo.nombre.value.trim(),
            categoria_id: Number(formPlatillo.categoria_id.value),
            precio: Number(formPlatillo.precio.value),
            descripcion: formPlatillo.descripcion.value.trim() || null,
            imagen_url: formPlatillo.imagen_url.value.trim() || null,
            disponible: formPlatillo.disponible.checked,
        };
        await conBoton(formPlatillo.querySelector('[type="submit"]'), async () => {
            try {
                if (editando) await api.put(`/productos/${editando.id}`, datos);
                else await api.post('/productos', datos);
                dlgPlatillo.close();
                avisar(editando ? `«${datos.nombre}» actualizado.` : `«${datos.nombre}» agregado al menú.`);
                await vista.render(vista.currentPage);
            } catch (error) {
                mostrarErrorForm(dlgPlatillo, mensajeError(error));
            }
        });
    });

    document.querySelector('[data-nuevo-platillo]').addEventListener('click', () => abrirFormulario());

    container.addEventListener('click', async (e) => {
        const boton = e.target.closest('[data-editar], [data-alternar], [data-eliminar]');
        if (!boton) return;
        const id = boton.dataset.editar ?? boton.dataset.alternar ?? boton.dataset.eliminar;
        const producto = vista.items.find((p) => String(p.id) === id);
        if (!producto) return;

        if (boton.dataset.editar) { await abrirFormulario(producto); return; }

        if (boton.dataset.alternar) {
            await conBoton(boton, async () => {
                try {
                    await api.put(`/productos/${producto.id}`, { disponible: !producto.disponible });
                    avisar(`«${producto.nombre}» ${producto.disponible ? 'marcado como agotado' : 'disponible otra vez'}.`);
                    await vista.render(vista.currentPage);
                } catch (error) {
                    avisar(mensajeError(error));
                }
            });
            return;
        }

        const acepta = await confirmar({
            titulo: `Eliminar «${producto.nombre}»`,
            mensaje: 'El platillo se retira del menú. Si ya se vendió en alguna comanda no se puede eliminar: en ese caso marcalo como agotado.',
            aceptar: 'Eliminar', peligro: true,
        });
        if (!acepta) return;
        try {
            await api.delete(`/productos/${producto.id}`);
            avisar(`«${producto.nombre}» eliminado del menú.`);
            await vista.render(vista.currentPage);
        } catch (error) {
            avisar(mensajeError(error));
        }
    });

    const dlgCategorias = document.getElementById('dlg-categorias');
    const listaCategorias = dlgCategorias.querySelector('[data-categorias-lista]');

    function pintarCategorias() {
        listaCategorias.innerHTML = categorias.length ? categorias.map((c) => `
            <li class="cat-fila" data-cat="${escapar(c.id)}">
                <input type="text" value="${escapar(c.nombre)}" maxlength="100" aria-label="Nombre de la categoría ${escapar(c.nombre)}">
                <button type="button" class="btn btn--outline" data-cat-guardar>Guardar</button>
                <button type="button" class="btn btn--ghost btn--peligro" data-cat-eliminar>Eliminar</button>
            </li>`).join('') : '<li class="muted">Todavía no hay categorías.</li>';
    }

    document.querySelector('[data-abrir-categorias]').addEventListener('click', async () => {
        try { await cargarCategorias(); } catch (error) { avisar(mensajeError(error)); return; }
        pintarCategorias();
        mostrarErrorForm(dlgCategorias, '');
        dlgCategorias.showModal();
    });

    async function recargarTodo() {
        await cargarCategorias();
        pintarCategorias();
        await vista.render(vista.currentPage);
    }

    dlgCategorias.querySelector('[data-form-categoria]').addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = e.currentTarget.nombre;
        const nombre = input.value.trim();
        if (!nombre) { mostrarErrorForm(dlgCategorias, 'Escribí el nombre de la categoría.'); return; }
        mostrarErrorForm(dlgCategorias, '');
        try {
            await api.post('/categorias', { nombre });
            input.value = '';
            await recargarTodo();
            avisar(`Categoría «${nombre}» creada.`);
        } catch (error) {
            mostrarErrorForm(dlgCategorias, mensajeError(error));
        }
    });

    listaCategorias.addEventListener('click', async (e) => {
        const fila = e.target.closest('[data-cat]');
        const boton = e.target.closest('button');
        if (!fila || !boton) return;
        const id = fila.dataset.cat;
        const categoria = categorias.find((c) => String(c.id) === id);
        mostrarErrorForm(dlgCategorias, '');

        try {
            if (boton.hasAttribute('data-cat-guardar')) {
                const nombre = fila.querySelector('input').value.trim();
                if (!nombre) { mostrarErrorForm(dlgCategorias, 'El nombre no puede quedar vacío.'); return; }
                await api.put(`/categorias/${id}`, { nombre });
                await recargarTodo();
                avisar(`Categoría «${nombre}» guardada.`);
            } else if (boton.hasAttribute('data-cat-eliminar')) {
                const acepta = await confirmar({
                    titulo: `Eliminar la categoría «${categoria.nombre}»`,
                    mensaje: 'Solo se puede eliminar si no tiene platillos asociados.',
                    aceptar: 'Eliminar', peligro: true,
                });
                if (!acepta) return;
                await api.delete(`/categorias/${id}`);
                await recargarTodo();
                avisar(`Categoría «${categoria.nombre}» eliminada.`);
            }
        } catch (error) {
            mostrarErrorForm(dlgCategorias, mensajeError(error));
        }
    });
});
