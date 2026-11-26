/**
 * Pantalla de Reservaciones conectada a la API (/api/reservas).
 *   - administrador y mesero: registran, confirman y cancelan reservas.
 *   - cajero: solo consulta.
 *   - administrador: además puede eliminar una reserva.
 * El servidor valida capacidad de la mesa y choques de horario (409/422) y aquí se muestra su mensaje.
 */
import {
    api, asegurarRol, avisar, confirmar, conBoton, escapar, etiquetaFecha, fechaIso, mensajeError,
    mostrarErrorForm, pintarCargando, pintarError, tieneRol, traerTodo,
} from '../core.js';

const BADGES = {
    pendiente: ['Pendiente', 'warning'],
    confirmada: ['Confirmada', 'success'],
    cumplida: ['Cumplida', 'info'],
    cancelada: ['Cancelada', 'neutral'],
};

const estado = { reservas: [], mesas: [], rango: 'todas', verCerradas: false };

const $lista = document.querySelector('[data-lista-reservas]');
const form = document.querySelector('[data-form-reserva]');
const puedeGestionar = () => tieneRol('administrador', 'mesero');

/* ------------------------------------------------------------------ Lista */

function reservasVisibles() {
    const hoy = fechaIso();
    const manana = fechaIso(new Date(Date.now() + 86400000));
    return estado.reservas.filter((r) => {
        if (!estado.verCerradas && !['pendiente', 'confirmada'].includes(r.estado)) return false;
        if (estado.rango === 'hoy') return r.fecha === hoy;
        if (estado.rango === 'manana') return r.fecha === manana;
        return true;
    });
}

function opcionesMesa(reserva) {
    const aptas = estado.mesas.filter((m) => m.capacidad >= reserva.personas);
    return `<option value="">Asignar mesa…</option>${aptas.map((m) => `<option value="${m.id}">Mesa ${m.numero} · ${escapar(m.zona)} · ${m.capacidad} pax</option>`).join('')}`;
}

function filaReserva(r) {
    const [textoEstado, tono] = BADGES[r.estado] ?? [r.estado, 'neutral'];
    const activa = ['pendiente', 'confirmada'].includes(r.estado);
    const detalle = [
        `${r.personas} ${r.personas === 1 ? 'persona' : 'personas'}`,
        r.mesa ? `Mesa ${r.mesa.numero} (${escapar(r.mesa.zona)})` : (r.zona ? `Zona: ${escapar(r.zona)}` : null),
        r.telefono ? escapar(r.telefono) : null,
    ].filter(Boolean).join(' · ');

    const acciones = [];
    if (puedeGestionar() && activa) {
        if (r.estado === 'pendiente') acciones.push(`<button type="button" class="btn btn--outline" data-r-confirmar="${r.id}">Confirmar</button>`);
        acciones.push(`<button type="button" class="btn btn--ghost" data-r-cancelar="${r.id}">Cancelar reserva</button>`);
        if (!r.mesa_id) acciones.push(`<select class="select" data-r-mesa="${r.id}" aria-label="Asignar mesa a la reserva de ${escapar(r.nombre)}">${opcionesMesa(r)}</select>`);
    }
    if (tieneRol('administrador') && !activa) {
        acciones.push(`<button type="button" class="btn btn--ghost btn--peligro" data-r-eliminar="${r.id}">Eliminar</button>`);
    }

    return `
        <li class="reserva ${activa ? '' : 'reserva--cerrada'}">
            <span class="reserva-hora"><strong>${escapar(r.hora)}</strong><small>${escapar(etiquetaFecha(r.fecha))}</small></span>
            <span class="reserva-info">
                <strong>${escapar(r.nombre)}</strong>
                <span>${detalle}</span>
                ${r.notas ? `<span class="item-note" style="margin-top:4px;">${escapar(r.notas)}</span>` : ''}
            </span>
            <span class="badge badge--${tono}">${textoEstado}</span>
            ${acciones.length ? `<span class="reserva-acciones">${acciones.join('')}</span>` : ''}
        </li>`;
}

function pintarLista() {
    const visibles = reservasVisibles();
    if (visibles.length === 0) {
        $lista.innerHTML = `<p class="empty-state">${estado.reservas.length === 0
            ? 'Todavía no hay reservas registradas.' : 'No hay reservas con ese filtro.'}</p>`;
        return;
    }
    $lista.innerHTML = `<ul class="reserva-list">${visibles.map(filaReserva).join('')}</ul>`;
}

async function cargar({ silencioso = false } = {}) {
    if (!silencioso) pintarCargando($lista, 'Cargando reservas…');
    try {
        const [reservas, mesas] = await Promise.all([
            traerTodo(`/reservas?desde=${fechaIso()}&por_pagina=100`),
            traerTodo('/mesas?por_pagina=100'),
        ]);
        estado.reservas = reservas;
        estado.mesas = mesas;
        pintarFormularioMesas();
        pintarLista();
    } catch (error) {
        pintarError($lista, mensajeError(error), () => cargar());
    }
}

/* ------------------------------------------------------------------ Formulario */

function pintarFormularioMesas() {
    if (!form) return;
    const zona = form.zona;
    const zonas = [...new Set(estado.mesas.map((m) => m.zona).filter(Boolean))];
    const zonaActual = zona.value;
    zona.innerHTML = `<option value="">Cualquiera</option>${zonas.map((z) => `<option value="${escapar(z)}">${escapar(z)}</option>`).join('')}`;
    zona.value = zonas.includes(zonaActual) ? zonaActual : '';
    actualizarMesasDelFormulario();
}

/** Lista solo las mesas donde caben los comensales y que son de la zona elegida. */
function actualizarMesasDelFormulario() {
    const personas = Number(form.personas.value) || 1;
    const aptas = estado.mesas.filter((m) => m.capacidad >= personas && (!form.zona.value || m.zona === form.zona.value));
    const actual = form.mesa_id.value;
    form.mesa_id.innerHTML = `<option value="">Sin asignar todavía</option>${aptas.map((m) => `<option value="${m.id}">Mesa ${m.numero} · ${escapar(m.zona)} · ${m.capacidad} pax</option>`).join('')}`;
    if (aptas.some((m) => String(m.id) === actual)) form.mesa_id.value = actual;
    document.querySelector('[data-mesa-hint]').textContent = aptas.length
        ? 'Solo se listan mesas con capacidad suficiente. Si la mesa choca con otra reserva, el sistema te avisa.'
        : 'No hay mesas con esa capacidad en la zona elegida; podés dejarla sin asignar.';
}

function iniciarFormulario() {
    if (!form) return;
    const hoy = fechaIso();
    form.fecha.min = hoy;
    form.fecha.value = hoy;

    form.personas.addEventListener('input', actualizarMesasDelFormulario);
    form.zona.addEventListener('change', actualizarMesasDelFormulario);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const contenedor = form.closest('[data-roles]') ?? form;
        if (!form.checkValidity()) {
            const invalido = form.querySelector(':invalid');
            const etiqueta = form.querySelector(`label[for="${invalido.id}"]`)?.firstChild.textContent.trim() ?? invalido.name;
            mostrarErrorForm(contenedor, `Revisá el campo «${etiqueta}».`);
            invalido.focus();
            return;
        }
        mostrarErrorForm(contenedor, '');

        const datos = {
            nombre: form.nombre.value.trim(),
            correo: form.correo.value.trim() || null,
            telefono: form.telefono.value.trim() || null,
            fecha: form.fecha.value,
            hora: form.hora.value,
            personas: Number(form.personas.value),
            zona: form.zona.value || null,
            mesa_id: form.mesa_id.value ? Number(form.mesa_id.value) : null,
            notas: form.notas.value.trim() || null,
        };

        await conBoton(form.querySelector('[type="submit"]'), async () => {
            try {
                await api.post('/reservas', datos);
                avisar(`Reserva registrada para ${datos.nombre} (${etiquetaFecha(datos.fecha)}, ${datos.hora}).`);
                form.reset();
                form.fecha.value = hoy;
                form.hora.value = '19:00';
                form.personas.value = 2;
                await cargar({ silencioso: true });
            } catch (error) {
                mostrarErrorForm(contenedor, mensajeError(error));
            }
        });
    });
}

/* ------------------------------------------------------------------ Acciones sobre una reserva */

async function actualizar(id, cambios, mensaje) {
    await api.put(`/reservas/${id}`, cambios);
    await cargar({ silencioso: true });
    if (mensaje) avisar(mensaje);
}

$lista.addEventListener('click', async (e) => {
    const boton = e.target.closest('button');
    if (!boton) return;
    const id = boton.dataset.rConfirmar ?? boton.dataset.rCancelar ?? boton.dataset.rEliminar;
    const reserva = estado.reservas.find((r) => String(r.id) === id);
    if (!reserva) return;

    try {
        if (boton.dataset.rConfirmar) {
            await conBoton(boton, () => actualizar(reserva.id, { estado: 'confirmada' }, `Reserva de ${reserva.nombre} confirmada.`));
        } else if (boton.dataset.rCancelar) {
            const acepta = await confirmar({
                titulo: `Cancelar la reserva de ${reserva.nombre}`,
                mensaje: `${etiquetaFecha(reserva.fecha)} a las ${reserva.hora}. La mesa queda disponible para ese horario.`,
                aceptar: 'Cancelar reserva', peligro: true,
            });
            if (acepta) await actualizar(reserva.id, { estado: 'cancelada' }, `Reserva de ${reserva.nombre} cancelada.`);
        } else if (boton.dataset.rEliminar) {
            const acepta = await confirmar({
                titulo: `Eliminar la reserva de ${reserva.nombre}`,
                mensaje: 'Se borra del historial. Esta acción no se puede deshacer.',
                aceptar: 'Eliminar', peligro: true,
            });
            if (acepta) {
                await api.delete(`/reservas/${reserva.id}`);
                await cargar({ silencioso: true });
                avisar('Reserva eliminada.');
            }
        }
    } catch (error) {
        avisar(mensajeError(error));
    }
});

$lista.addEventListener('change', async (e) => {
    const select = e.target.closest('[data-r-mesa]');
    if (!select || !select.value) return;
    const reserva = estado.reservas.find((r) => String(r.id) === select.dataset.rMesa);
    try {
        await actualizar(reserva.id, { mesa_id: Number(select.value) }, `Mesa asignada a la reserva de ${reserva.nombre}.`);
    } catch (error) {
        avisar(mensajeError(error));
        select.value = '';
    }
});

/* ------------------------------------------------------------------ Filtros */

document.querySelector('[data-grupo-rango]').addEventListener('click', (e) => {
    const chip = e.target.closest('[data-rango]');
    if (!chip) return;
    estado.rango = chip.dataset.rango;
    e.currentTarget.querySelectorAll('[data-rango]').forEach((c) => c.setAttribute('aria-pressed', String(c === chip)));
    pintarLista();
});

document.querySelector('[data-ver-cerradas]').addEventListener('change', (e) => {
    estado.verCerradas = e.target.checked;
    pintarLista();
});

/* ------------------------------------------------------------------ Arranque */

(async () => {
    await asegurarRol();
    iniciarFormulario();
    await cargar();
})();
