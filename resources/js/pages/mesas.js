import {
    api, asegurarRol, avisar, colones, conBoton, confirmar, escapar, fechaIso, formatoDuracion, mensajeError,
    minutosDesde, mostrarErrorForm, pintarCargando, pintarError, tieneRol, traerTodo, usuario,
} from '../core.js';

const MINUTOS_RETRASO = 45;
const ACTUALIZAR_CADA_MS = 30000;

const ICONOS = {
    personas: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    ubicacion: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>',
    reloj: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
};

const estado = {
    mesas: [],
    reservasHoy: [],
    filtros: { zona: '', estado: '', texto: '' },
    cargado: false,
};

const contenedor = document.getElementById('mesas-contenedor');
const puedeAbrir = () => tieneRol('administrador', 'mesero');
const esAdmin = () => tieneRol('administrador');

function reservasDeMesa(mesa) {
    return estado.reservasHoy
        .filter((r) => r.mesa_id === mesa.id)
        .sort((a, b) => a.hora.localeCompare(b.hora));
}

function minutosEnMesa(mesa) {
    return minutosDesde(mesa.comanda_activa?.abierta_en);
}

function estadoVisual(mesa) {
    const codigo = mesa.estado?.codigo;
    if (codigo === 'ocupada' || mesa.comanda_activa) return minutosEnMesa(mesa) > MINUTOS_RETRASO ? 'retraso' : 'ocupada';
    if (codigo === 'reservada' || reservasDeMesa(mesa).length > 0) return 'reservada';
    return 'libre';
}

function puedeVerComanda(mesa) {
    const c = mesa.comanda_activa;
    if (!c) return false;
    return tieneRol('administrador', 'cajero') || (tieneRol('mesero') && c.mesero_id === usuario()?.id);
}

function textoBuscable(mesa) {
    const reserva = reservasDeMesa(mesa)[0];
    return `mesa ${mesa.numero} ${mesa.zona} ${mesa.comanda_activa?.mesero ?? ''} ${reserva?.nombre ?? ''}`.toLowerCase();
}

function mesasFiltradas() {
    const { zona, estado: est, texto } = estado.filtros;
    const coincideEstado = (m) => !est || estadoVisual(m) === est || (est === 'ocupada' && estadoVisual(m) === 'retraso');
    return estado.mesas.filter((m) => (!zona || m.zona === zona) && coincideEstado(m)
        && (!texto || textoBuscable(m).includes(texto)));
}

function pintarKpis() {
    const total = estado.mesas.length;
    const visuales = estado.mesas.map(estadoVisual);
    const ocupadas = visuales.filter((v) => v === 'ocupada' || v === 'retraso').length;
    const reservadas = visuales.filter((v) => v === 'reservada').length;
    const libres = visuales.filter((v) => v === 'libre').length;
    const facturacion = estado.mesas.reduce((suma, m) => suma + (m.comanda_activa?.total ?? 0), 0);

    const poner = (clave, valor) => { document.querySelector(`[data-kpi="${clave}"]`).textContent = valor; };
    poner('libres', libres);
    poner('ocupadas', ocupadas);
    poner('reservadas', reservadas);
    poner('ocupacion', `${Math.round((ocupadas / Math.max(total, 1)) * 100)}%`);
    poner('facturacion', colones(facturacion));
}

function pintarZonas() {
    const grupo = document.querySelector('[data-zonas]');
    const zonas = [...new Set(estado.mesas.map((m) => m.zona).filter(Boolean))];
    if (estado.filtros.zona && !zonas.includes(estado.filtros.zona)) estado.filtros.zona = '';

    grupo.innerHTML = ['', ...zonas].map((z) => `
        <button type="button" class="chip" data-zona="${escapar(z)}" aria-pressed="${z === estado.filtros.zona}">${escapar(z || 'Todas')}</button>`).join('');

    document.getElementById('lista-zonas').innerHTML = zonas.map((z) => `<option value="${escapar(z)}"></option>`).join('');
}

function pintarAvisoReservasSinMesa() {
    const caja = document.querySelector('[data-aviso-reservas]');
    const sinMesa = estado.reservasHoy.filter((r) => !r.mesa_id);
    caja.hidden = sinMesa.length === 0;
    if (!sinMesa.length) return;
    const lista = sinMesa.map((r) => `${r.nombre} (${r.hora}, ${r.personas} pax)`).join(', ');
    caja.querySelector('[data-aviso-reservas-texto]').textContent = `Reservas de hoy sin mesa asignada: ${lista}. Asignales una mesa desde Reservaciones.`;
}

function tarjetaMesa(mesa) {
    const visual = estadoVisual(mesa);
    const c = mesa.comanda_activa;
    const reserva = reservasDeMesa(mesa)[0];
    const badge = {
        libre: ['Libre', 'success'], ocupada: ['Ocupada', 'warning'], retraso: ['Retraso', 'danger'], reservada: ['Reservada', 'neutral'],
    }[visual];
    const claseEstado = visual === 'retraso' ? 'mesa--ocupada mesa--retraso' : `mesa--${visual}`;

    let cuerpo;
    if (c) {
        cuerpo = `
            <span class="mesa-time">${ICONOS.reloj} ${escapar(formatoDuracion(minutosEnMesa(mesa)))}</span>
            <p class="mesa-amount">${colones(c.total)}</p>
            <p class="muted" style="font-size:12.5px;">Mesero: ${escapar(c.mesero ?? '—')}</p>
            <div class="mesa-actions">
                ${puedeVerComanda(mesa)
        ? `<a class="btn btn--primary" href="/pedidos#${c.id}">Ver comanda</a>`
        : '<button type="button" class="btn btn--outline" disabled>Atiende otro mesero</button>'}
                <button type="button" class="btn btn--ghost" data-accion="detalle" data-mesa="${mesa.id}">Detalle</button>
            </div>`;
    } else if (visual === 'reservada') {
        cuerpo = `
            <p class="mesa-reserva"><strong>${escapar(reserva?.nombre ?? 'Reservada')}</strong>
                <span class="mesa-meta"><span>${ICONOS.reloj} Hora: ${escapar(reserva?.hora ?? '—')}</span></span></p>
            <div class="mesa-actions">
                ${puedeAbrir() && reserva
        ? `<button type="button" class="btn btn--outline" data-accion="recibir" data-mesa="${mesa.id}">Recibir reserva</button>` : ''}
                ${puedeAbrir() ? `<button type="button" class="btn btn--ghost" data-accion="abrir" data-mesa="${mesa.id}">Abrir sin reserva</button>` : ''}
                <button type="button" class="btn btn--ghost" data-accion="detalle" data-mesa="${mesa.id}">Detalle</button>
            </div>`;
    } else {
        cuerpo = `
            <p class="mesa-status">Disponible</p>
            <div class="mesa-actions">
                ${puedeAbrir() ? `<button type="button" class="btn btn--primary" data-accion="abrir" data-mesa="${mesa.id}">Abrir comanda</button>` : ''}
                <button type="button" class="btn btn--ghost" data-accion="detalle" data-mesa="${mesa.id}">Detalle</button>
            </div>`;
    }

    return `
        <article class="mesa ${claseEstado}">
            <div class="mesa-head">
                <h2 class="mesa-num"><span class="sr-only">Mesa </span>${escapar(mesa.numero)}</h2>
                <span class="badge badge--${badge[1]}">${badge[0]}</span>
            </div>
            <div class="mesa-meta">
                <span>${ICONOS.personas} ${escapar(mesa.capacidad)} pax</span>
                <span>${ICONOS.ubicacion} ${escapar(mesa.zona)}</span>
            </div>
            ${cuerpo}
        </article>`;
}

function pintarLista() {
    const visibles = mesasFiltradas();
    if (estado.mesas.length === 0) {
        contenedor.innerHTML = `<div class="state-container"><p><strong>Todavía no hay mesas registradas</strong></p>
            <p>${esAdmin() ? 'Creá la primera con «Nueva mesa».' : 'Pedile al administrador que registre las mesas del salón.'}</p></div>`;
        return;
    }
    if (visibles.length === 0) {
        contenedor.innerHTML = '<p class="empty-state">No hay mesas que coincidan con los filtros.</p>';
        return;
    }
    contenedor.innerHTML = `<section class="mesas-grid" aria-label="Mesas del salón">${visibles.map(tarjetaMesa).join('')}</section>`;
}

function pintarTodo() {
    pintarKpis();
    pintarZonas();
    pintarAvisoReservasSinMesa();
    pintarLista();
}

async function cargar({ silencioso = false } = {}) {
    if (!silencioso) pintarCargando(contenedor, 'Cargando el salón…');
    try {
        const [mesas, reservas] = await Promise.all([
            traerTodo('/mesas?por_pagina=100'),
            traerTodo(`/reservas?fecha=${fechaIso()}&por_pagina=100`),
        ]);
        estado.mesas = mesas;
        estado.reservasHoy = reservas.filter((r) => ['pendiente', 'confirmada'].includes(r.estado));
        estado.cargado = true;
        pintarTodo();
    } catch (error) {
        if (silencioso && estado.cargado) return;
        pintarError(contenedor, mensajeError(error), () => cargar());
    }
}

function abrirDetalle(mesa) {
    const dialogo = document.getElementById('dlg-mesa');
    const c = mesa.comanda_activa;
    const reservas = reservasDeMesa(mesa);
    const visual = estadoVisual(mesa);
    const nombreEstado = { libre: 'Libre', ocupada: 'Ocupada', retraso: 'Ocupada (con retraso)', reservada: 'Reservada' }[visual];

    const filas = [`<div class="modal-row"><dt>Estado</dt><dd>${nombreEstado}</dd></div>`];
    if (c) {
        filas.push(
            `<div class="modal-row"><dt>Comanda</dt><dd>#${escapar(c.id)}</dd></div>`,
            `<div class="modal-row"><dt>Personas</dt><dd>${escapar(c.personas ?? '—')}</dd></div>`,
            `<div class="modal-row"><dt>Mesero</dt><dd>${escapar(c.mesero ?? '—')}</dd></div>`,
            `<div class="modal-row"><dt>Tiempo en mesa</dt><dd>${escapar(formatoDuracion(minutosEnMesa(mesa)))}</dd></div>`,
            `<div class="modal-row"><dt>Productos</dt><dd>${escapar(c.items)}</dd></div>`,
            `<div class="modal-row"><dt>Total actual</dt><dd>${colones(c.total)}</dd></div>`,
        );
    } else if (reservas.length) {
        reservas.forEach((r) => filas.push(
            `<div class="modal-row"><dt>Reserva ${escapar(r.hora)}</dt><dd>${escapar(r.nombre)} · ${escapar(r.personas)} pax</dd></div>`,
        ));
    } else {
        filas.push('<div class="modal-row"><dt>Comanda</dt><dd>Sin orden activa</dd></div>');
    }

    dialogo.innerHTML = `
        <div class="modal-head">
            <div>
                <h2 id="dlg-mesa-titulo">Mesa ${escapar(mesa.numero)}</h2>
                <p class="muted">${escapar(mesa.zona)} · capacidad ${escapar(mesa.capacidad)} personas</p>
            </div>
            <button type="button" class="icon-btn" data-cerrar aria-label="Cerrar">&times;</button>
        </div>
        <dl class="modal-body">${filas.join('')}</dl>
        <div class="modal-foot">
            ${esAdmin() ? `<button type="button" class="btn btn--outline" data-accion="editar" data-mesa="${mesa.id}">Editar mesa</button><span class="toolbar-spacer"></span>` : ''}
            ${puedeVerComanda(mesa) ? `<a class="btn btn--primary" href="/pedidos#${c.id}">Ir a la comanda</a>` : ''}
            <button type="button" class="btn btn--ghost" data-cerrar>Cerrar</button>
        </div>`;
    dialogo.setAttribute('aria-labelledby', 'dlg-mesa-titulo');
    dialogo.showModal();
}

const dialogoAbrir = document.getElementById('dlg-abrir');
const formAbrir = dialogoAbrir.querySelector('form');
let contextoAbrir = null;

function abrirDialogoComanda(mesa, reserva = null) {
    contextoAbrir = { mesa, reserva };
    dialogoAbrir.querySelector('[data-abrir-sub]').textContent = reserva
        ? `Mesa ${mesa.numero} · reserva de ${reserva.nombre} (${reserva.hora})`
        : `Mesa ${mesa.numero} · ${mesa.zona} · capacidad ${mesa.capacidad}`;
    formAbrir.personas.value = reserva?.personas ?? Math.min(2, mesa.capacidad);
    formAbrir.personas.max = Math.max(mesa.capacidad, 1) + 10;
    formAbrir.notas.value = reserva?.notas ?? '';
    mostrarErrorForm(dialogoAbrir, '');
    dialogoAbrir.showModal();
    formAbrir.personas.focus();
}

formAbrir.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!formAbrir.checkValidity()) {
        mostrarErrorForm(dialogoAbrir, 'Indicá cuántas personas hay en la mesa (al menos 1).');
        return;
    }
    mostrarErrorForm(dialogoAbrir, '');
    const { mesa, reserva } = contextoAbrir;
    const boton = formAbrir.querySelector('[type="submit"]');

    await conBoton(boton, async () => {
        try {
            const comanda = await api.post('/comandas', {
                mesa_id: mesa.id,
                user_id: usuario().id,
                personas: Number(formAbrir.personas.value),
                notas: formAbrir.notas.value.trim() || null,
            });

            if (reserva) {
                await api.put(`/reservas/${reserva.id}`, { estado: 'cumplida' })
                    .catch(() => avisar('La comanda se abrió, pero no se pudo marcar la reserva como cumplida.'));
            }

            window.location.assign(`/pedidos#${comanda.data.id}`);
        } catch (error) {
            mostrarErrorForm(dialogoAbrir, mensajeError(error));
            if (error.codigo === 'mesa_no_disponible') cargar({ silencioso: true });
        }
    });
});

const dialogoMesa = document.getElementById('dlg-mesa-form');
const formMesa = dialogoMesa.querySelector('form');
let mesaEnEdicion = null;

function abrirFormMesa(mesa = null) {
    mesaEnEdicion = mesa;
    dialogoMesa.querySelector('[data-mesa-form-titulo]').textContent = mesa ? `Editar mesa ${mesa.numero}` : 'Nueva mesa';
    formMesa.numero.value = mesa?.numero ?? (Math.max(0, ...estado.mesas.map((m) => m.numero)) + 1);
    formMesa.capacidad.value = mesa?.capacidad ?? 4;
    formMesa.zona.value = mesa?.zona ?? (estado.filtros.zona || 'Salón');
    dialogoMesa.querySelector('[data-mesa-eliminar]').hidden = !mesa;
    mostrarErrorForm(dialogoMesa, '');
    document.getElementById('dlg-mesa')?.close();
    dialogoMesa.showModal();
}

formMesa.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!formMesa.checkValidity()) {
        mostrarErrorForm(dialogoMesa, 'Revisá el número, la capacidad y la zona de la mesa.');
        return;
    }
    const datos = {
        numero: Number(formMesa.numero.value),
        capacidad: Number(formMesa.capacidad.value),
        zona: formMesa.zona.value.trim(),
    };
    await conBoton(formMesa.querySelector('[type="submit"]'), async () => {
        try {
            if (mesaEnEdicion) await api.put(`/mesas/${mesaEnEdicion.id}`, datos);
            else await api.post('/mesas', datos);
            dialogoMesa.close();
            avisar(mesaEnEdicion ? `Mesa ${datos.numero} actualizada.` : `Mesa ${datos.numero} creada.`);
            await cargar({ silencioso: true });
        } catch (error) {
            mostrarErrorForm(dialogoMesa, mensajeError(error));
        }
    });
});

dialogoMesa.querySelector('[data-mesa-eliminar]').addEventListener('click', async () => {
    if (!mesaEnEdicion) return;
    const mesa = mesaEnEdicion;
    const acepta = await confirmar({
        titulo: `Eliminar la mesa ${mesa.numero}`,
        mensaje: 'La mesa desaparece del salón. Solo se puede eliminar si nunca tuvo comandas.',
        aceptar: 'Eliminar', peligro: true,
    });
    if (!acepta) return;
    try {
        await api.delete(`/mesas/${mesa.id}`);
        dialogoMesa.close();
        avisar(`Mesa ${mesa.numero} eliminada.`);
        await cargar({ silencioso: true });
    } catch (error) {
        mostrarErrorForm(dialogoMesa, mensajeError(error));
    }
});

function mesaDe(elemento) {
    return estado.mesas.find((m) => String(m.id) === elemento.dataset.mesa);
}

contenedor.addEventListener('click', (e) => {
    const boton = e.target.closest('[data-accion]');
    if (!boton) return;
    const mesa = mesaDe(boton);
    if (!mesa) return;

    if (boton.dataset.accion === 'detalle') abrirDetalle(mesa);
    if (boton.dataset.accion === 'abrir') abrirDialogoComanda(mesa);
    if (boton.dataset.accion === 'recibir') abrirDialogoComanda(mesa, reservasDeMesa(mesa)[0]);
});

document.getElementById('dlg-mesa').addEventListener('click', (e) => {
    const boton = e.target.closest('[data-accion="editar"]');
    if (boton) abrirFormMesa(mesaDe(boton));
});

document.querySelector('[data-nueva-mesa]').addEventListener('click', () => abrirFormMesa());

document.querySelector('[data-zonas]').addEventListener('click', (e) => {
    const chip = e.target.closest('[data-zona]');
    if (!chip) return;
    estado.filtros.zona = chip.dataset.zona;
    pintarZonas();
    pintarLista();
});

document.querySelector('[data-filtro-estado]').addEventListener('change', (e) => {
    estado.filtros.estado = e.target.value;
    pintarLista();
});

document.querySelector('[data-filtro-buscar]').addEventListener('input', (e) => {
    estado.filtros.texto = e.target.value.trim().toLowerCase();
    pintarLista();
});

(async () => {
    await asegurarRol();
    await cargar();

    setInterval(() => {
        if (document.hidden || document.querySelector('dialog[open]')) return;
        cargar({ silencioso: true });
    }, ACTUALIZAR_CADA_MS);
})();
