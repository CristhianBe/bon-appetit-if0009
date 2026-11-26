/**
 * Pantalla de Comandas: comandas activas reales (GET /api/comandas?activas=1) con su detalle.
 *
 * Qué puede hacer cada rol (la API vuelve a validarlo, esto solo evita mostrar botones inútiles):
 *   - mesero:        agrega/quita productos, envía a cocina y marca servida SUS comandas.
 *   - cajero:        ve todas y cobra (POST /api/comandas/{id}/cobrar).
 *   - administrador: todo lo anterior sobre cualquier comanda.
 */
import {
    api, asegurarRol, avisar, colones, conBoton, confirmar, escapar, formatoDuracion, mensajeError, minutosDesde,
    mostrarErrorForm, pintarCargando, pintarError, tieneRol, traerTodo, usuario,
} from '../core.js';

const MINUTOS_RETRASO = 45;
const ACTUALIZAR_CADA_MS = 30000;
const ICONO_RELOJ = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';

const ESTADOS = {
    abierta: { texto: 'Abierta', tono: 'info' },
    en_preparacion: { texto: 'En preparación', tono: 'warning' },
    servida: { texto: 'Servida', tono: 'success' },
};

const estado = {
    comandas: [],
    estadosId: {},        // codigo → id (para cambiar el estado de una comanda)
    metodos: [],
    productos: null,      // se carga la primera vez que se abre el selector
    seleccionada: null,   // id de la comanda que se ve a la derecha
    filtros: { estado: '', texto: '' },
    cargado: false,
};

const $lista = document.querySelector('[data-lista]');
const $detalle = document.querySelector('[data-detalle-contenedor]');

/* ------------------------------------------------------------------ Permisos y cálculos */

const esMia = (c) => c.user_id === usuario()?.id;
const puedeEditar = (c) => tieneRol('administrador') || (tieneRol('mesero') && esMia(c));
const puedeCobrar = () => tieneRol('administrador', 'cajero');

const codigoEstado = (c) => c.estado?.codigo ?? 'abierta';
const minutos = (c) => minutosDesde(c.creado_en);
const conRetraso = (c) => codigoEstado(c) !== 'servida' && minutos(c) > MINUTOS_RETRASO;
const totalDe = (c) => c.total_calculado ?? 0;
const cantidadItems = (c) => (c.detalles ?? []).reduce((n, d) => n + d.cantidad, 0);

function infoEstado(c) {
    if (conRetraso(c)) return { texto: 'Retraso', tono: 'danger' };
    return ESTADOS[codigoEstado(c)] ?? { texto: codigoEstado(c), tono: 'neutral' };
}

function textoBuscable(c) {
    return `${c.id} mesa ${c.mesa?.numero ?? ''} ${c.mesero?.name ?? ''}`.toLowerCase();
}

function comandasFiltradas() {
    const { estado: est, texto } = estado.filtros;
    return estado.comandas.filter((c) => (!est || codigoEstado(c) === est) && (!texto || textoBuscable(c).includes(texto)));
}

/* ------------------------------------------------------------------ Render: KPIs, filtros, lista */

function pintarKpis() {
    const lista = estado.comandas;
    const poner = (clave, valor) => { document.querySelector(`[data-kpi="${clave}"]`).textContent = valor; };
    poner('activas', lista.length);
    poner('facturacion', colones(lista.reduce((s, c) => s + totalDe(c), 0)));
    poner('preparacion', lista.filter((c) => codigoEstado(c) === 'en_preparacion').length);
    poner('promedio', lista.length ? formatoDuracion(Math.round(lista.reduce((s, c) => s + minutos(c), 0) / lista.length)) : '–');

    const atrasadas = lista.filter(conRetraso);
    const alerta = document.querySelector('[data-alerta-retraso]');
    alerta.hidden = atrasadas.length === 0;
    if (atrasadas.length) {
        const detalle = atrasadas.map((c) => `Mesa ${c.mesa?.numero} (${minutos(c)} min)`).join(', ');
        document.querySelector('[data-alerta-retraso-texto]').textContent =
            `${atrasadas.length} ${atrasadas.length === 1 ? 'comanda supera' : 'comandas superan'} los ${MINUTOS_RETRASO} min: ${detalle}.`;
    }
}

function pintarTabs() {
    const cuenta = (codigo) => estado.comandas.filter((c) => codigoEstado(c) === codigo).length;
    const tabs = [
        ['', 'Todas', estado.comandas.length],
        ['abierta', 'Abiertas', cuenta('abierta')],
        ['en_preparacion', 'En preparación', cuenta('en_preparacion')],
        ['servida', 'Servidas', cuenta('servida')],
    ];
    document.querySelector('[data-tabs]').innerHTML = tabs.map(([valor, texto, n]) => `
        <button type="button" class="chip" data-tab="${valor}" aria-pressed="${valor === estado.filtros.estado}">
            ${texto} <span class="count">(${n})</span>
        </button>`).join('');
}

function pintarLista() {
    const visibles = comandasFiltradas();

    if (estado.comandas.length === 0) {
        $lista.innerHTML = `<div class="state-container"><p><strong>No hay comandas activas</strong></p>
            <p>${tieneRol('cajero') ? 'Cuando un mesero abra una mesa, aparecerá aquí para cobrar.' : 'Abrí una comanda desde Mesas para empezar.'}</p>
            <a class="btn btn--primary" style="margin-top:12px;" href="/mesas">Ir a Mesas</a></div>`;
        return;
    }
    if (visibles.length === 0) {
        $lista.innerHTML = '<p class="empty-state">No hay comandas con ese filtro.</p>';
        return;
    }

    $lista.innerHTML = `<div class="comanda-list">${visibles.map((c) => {
        const info = infoEstado(c);
        return `
            <button type="button" class="comanda-card ${conRetraso(c) ? 'comanda-card--retraso' : ''}" data-comanda="${c.id}"
                    aria-current="${c.id === estado.seleccionada}">
                <span class="comanda-card-head">
                    <span class="comanda-card-title">Mesa ${escapar(c.mesa?.numero ?? '?')}</span>
                    <span class="badge badge--${info.tono}">${info.texto}</span>
                </span>
                <span class="comanda-card-sub" style="display:block;">#${c.id} · ${escapar(c.mesero?.name ?? '—')} · ${cantidadItems(c)} productos</span>
                <span class="comanda-card-foot">
                    <span class="mesa-time">${ICONO_RELOJ} ${escapar(formatoDuracion(minutos(c)))}</span>
                    <span class="comanda-card-total">${colones(totalDe(c))}</span>
                </span>
            </button>`;
    }).join('')}</div>`;
}

/* ------------------------------------------------------------------ Render: detalle */

function pintarDetalle() {
    const c = estado.comandas.find((x) => x.id === estado.seleccionada);
    if (!c) {
        $detalle.innerHTML = estado.comandas.length
            ? '<article class="panel-card detalle"><p class="empty-state">Elegí una comanda de la lista para ver su detalle.</p></article>'
            : '';
        return;
    }

    const editar = puedeEditar(c);
    const info = infoEstado(c);
    const detalles = c.detalles ?? [];
    const codigo = codigoEstado(c);

    const filas = detalles.length ? detalles.map((d) => `
        <li class="item">
            <span class="item-qty">${d.cantidad}×</span>
            <span>
                <span class="item-name">${escapar(d.producto?.nombre ?? `Producto #${d.producto_id}`)}</span><br>
                <span class="muted" style="font-size:12px;">${colones(d.precio_unitario)} c/u</span>
                ${d.notas ? `<br><span class="item-note">${escapar(d.notas)}</span>` : ''}
                ${editar ? `
                    <span class="item-controls">
                        <button type="button" class="stepper-btn" data-item-menos="${d.id}" aria-label="Quitar una unidad de ${escapar(d.producto?.nombre ?? 'producto')}">−</button>
                        <button type="button" class="stepper-btn" data-item-mas="${d.id}" aria-label="Agregar una unidad de ${escapar(d.producto?.nombre ?? 'producto')}">+</button>
                        <button type="button" class="stepper-btn stepper-btn--quitar" data-item-quitar="${d.id}" aria-label="Quitar ${escapar(d.producto?.nombre ?? 'producto')} de la comanda">Quitar</button>
                    </span>` : ''}
            </span>
            <span class="item-price">${colones(d.subtotal)}</span>
        </li>`).join('') : '<li class="item"><span></span><span class="muted">Todavía no hay productos en esta comanda.</span><span></span></li>';

    const acciones = [];
    if (editar) {
        acciones.push('<button type="button" class="btn btn--outline" data-accion="agregar">Agregar productos</button>');
        if (codigo === 'abierta') {
            acciones.push(`<button type="button" class="btn btn--outline" data-accion="cocina" ${detalles.length ? '' : 'disabled'}>Enviar a cocina</button>`);
        } else if (codigo === 'en_preparacion') {
            acciones.push('<button type="button" class="btn btn--outline" data-accion="servida">Marcar servida</button>');
        } else {
            acciones.push('<button type="button" class="btn btn--outline" disabled>Ya servida</button>');
        }
    }
    if (puedeCobrar()) {
        acciones.push(`<button type="button" class="btn btn--primary" data-accion="cobrar" ${detalles.length ? '' : 'disabled'}>Cobrar y cerrar</button>`);
    }

    let pie = '';
    if (!acciones.length) {
        pie = '<p class="muted" style="margin-top:14px;">Solo lectura: esta comanda la atiende otro mesero.</p>';
    }

    $detalle.innerHTML = `
        <article class="panel-card detalle" aria-labelledby="detalle-titulo" tabindex="-1">
            <header class="detalle-head">
                <div>
                    <h2 id="detalle-titulo">Mesa ${escapar(c.mesa?.numero ?? '?')}</h2>
                    <p class="muted">Comanda #${c.id} · ${escapar(c.mesero?.name ?? '—')}${c.personas ? ` · ${c.personas} pax` : ''} · ${escapar(formatoDuracion(minutos(c)))}</p>
                </div>
                <span class="badge badge--${info.tono}">${info.texto}</span>
            </header>

            <ul class="items">${filas}</ul>

            ${c.notas ? `<div class="alert alert--warning" style="margin:14px 0 0;"><span><strong>Notas:</strong> ${escapar(c.notas)}</span></div>` : ''}

            <div class="totales">
                <div><span>Subtotal</span><span>${colones(c.subtotal)}</span></div>
                ${c.descuento > 0 ? `<div><span>Descuento (10 % por consumo alto)</span><span>− ${colones(c.descuento)}</span></div>` : ''}
                <div class="total"><span>Total</span><strong>${colones(totalDe(c))}</strong></div>
            </div>

            ${acciones.length ? `<div class="detalle-actions">${acciones.join('')}</div>` : pie}
            ${editar ? '<p class="detalle-cancelar"><button type="button" class="link-peligro" data-accion="cancelar">Cancelar esta comanda</button></p>' : ''}
        </article>`;
}

function pintarTodo() {
    pintarKpis();
    pintarTabs();
    pintarLista();
    pintarDetalle();
}

function seleccionar(id, { desplazar = false } = {}) {
    estado.seleccionada = id;
    if (id) history.replaceState(null, '', `#${id}`);
    pintarLista();
    pintarDetalle();
    if (desplazar && window.matchMedia('(max-width: 1100px)').matches) {
        $detalle.querySelector('.detalle')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/* ------------------------------------------------------------------ Carga */

function ajustarSeleccion() {
    const enUrl = Number(decodeURIComponent(window.location.hash.slice(1))) || null;
    const existe = (id) => estado.comandas.some((c) => c.id === id);
    if (estado.seleccionada && existe(estado.seleccionada)) return;
    if (enUrl && existe(enUrl)) estado.seleccionada = enUrl;
    else estado.seleccionada = comandasFiltradas()[0]?.id ?? estado.comandas[0]?.id ?? null;
}

async function cargar({ silencioso = false } = {}) {
    if (!silencioso) pintarCargando($lista, 'Cargando comandas…');
    try {
        const [comandas, estados, metodos] = await Promise.all([
            traerTodo('/comandas?activas=1&por_pagina=50'),
            estado.cargado ? Promise.resolve(null) : traerTodo('/estados-comanda'),
            estado.cargado ? Promise.resolve(null) : traerTodo('/metodos-pago'),
        ]);
        estado.comandas = comandas;
        if (estados) estado.estadosId = Object.fromEntries(estados.map((e) => [e.codigo, e.id]));
        if (metodos) estado.metodos = metodos;
        estado.cargado = true;
        ajustarSeleccion();
        pintarTodo();
    } catch (error) {
        if (silencioso && estado.cargado) return;
        pintarError($lista, mensajeError(error), () => cargar());
    }
}

/** Vuelve a pedir una sola comanda (después de agregar un producto, cambiar de estado, etc.). */
async function refrescar(id) {
    const { data } = await api.get(`/comandas/${id}`);
    const i = estado.comandas.findIndex((c) => c.id === id);
    if (i >= 0) estado.comandas[i] = data;
    pintarTodo();
    return data;
}

/* ------------------------------------------------------------------ Acciones sobre la comanda */

const comandaActual = () => estado.comandas.find((c) => c.id === estado.seleccionada);

async function cambiarEstado(comanda, codigo, mensaje) {
    await api.put(`/comandas/${comanda.id}`, { estado_comanda_id: estado.estadosId[codigo] });
    await refrescar(comanda.id);
    avisar(mensaje);
}

async function ajustarCantidad(detalleId, delta) {
    const c = comandaActual();
    const d = c.detalles.find((x) => x.id === detalleId);
    if (!d) return;
    const nueva = d.cantidad + delta;
    if (nueva < 1) {
        await api.delete(`/detalles/${detalleId}`);
    } else {
        await api.put(`/detalles/${detalleId}`, { cantidad: nueva });
    }
    await refrescar(c.id);
}

async function cancelarComanda(comanda) {
    const acepta = await confirmar({
        titulo: `Cancelar la comanda #${comanda.id}`,
        mensaje: `Se borra la comanda de la mesa ${comanda.mesa?.numero} con todos sus productos y la mesa queda libre. Esta acción no se puede deshacer.`,
        aceptar: 'Cancelar comanda', peligro: true,
    });
    if (!acepta) return;
    await api.delete(`/comandas/${comanda.id}`);
    estado.seleccionada = null;
    history.replaceState(null, '', window.location.pathname);
    avisar(`Comanda #${comanda.id} cancelada. La mesa ${comanda.mesa?.numero} quedó libre.`);
    await cargar({ silencioso: true });
}

$detalle.addEventListener('click', async (e) => {
    const boton = e.target.closest('button');
    if (!boton) return;
    const c = comandaActual();
    if (!c) return;

    const ejecutar = (accion) => conBoton(boton, async () => {
        try { await accion(); } catch (error) { avisar(mensajeError(error)); if (error.status === 404) cargar({ silencioso: true }); }
    });

    if (boton.dataset.itemMas) await ejecutar(() => ajustarCantidad(Number(boton.dataset.itemMas), 1));
    else if (boton.dataset.itemMenos) await ejecutar(() => ajustarCantidad(Number(boton.dataset.itemMenos), -1));
    else if (boton.dataset.itemQuitar) await ejecutar(async () => { await api.delete(`/detalles/${boton.dataset.itemQuitar}`); await refrescar(c.id); });
    else if (boton.dataset.accion === 'agregar') abrirSelector(c);
    else if (boton.dataset.accion === 'cocina') await ejecutar(() => cambiarEstado(c, 'en_preparacion', `Comanda #${c.id} enviada a cocina.`));
    else if (boton.dataset.accion === 'servida') await ejecutar(() => cambiarEstado(c, 'servida', `Comanda #${c.id} marcada como servida.`));
    else if (boton.dataset.accion === 'cobrar') abrirCobro(c);
    else if (boton.dataset.accion === 'cancelar') await ejecutar(() => cancelarComanda(c));
});

/* ------------------------------------------------------------------ Selector de productos */

const dlgProductos = document.getElementById('dlg-productos');
const pickerFiltro = { texto: '', categoria: '' };

async function abrirSelector(comanda) {
    dlgProductos.querySelector('[data-productos-sub]').textContent = `Mesa ${comanda.mesa?.numero} · comanda #${comanda.id}`;
    dlgProductos.querySelector('[data-picker-nota]').value = '';
    dlgProductos.querySelector('[data-picker-buscar]').value = '';
    pickerFiltro.texto = '';
    pickerFiltro.categoria = '';
    mostrarErrorForm(dlgProductos, '');
    dlgProductos.showModal();

    const lista = dlgProductos.querySelector('[data-picker-lista]');
    if (!estado.productos) {
        pintarCargando(lista, 'Cargando el menú…');
        try {
            estado.productos = await traerTodo('/productos?por_pagina=50');
        } catch (error) {
            pintarError(lista, mensajeError(error), () => abrirSelector(comanda));
            return;
        }
    }
    pintarSelector();
}

function pintarSelector() {
    const c = comandaActual();
    const lista = dlgProductos.querySelector('[data-picker-lista]');
    const categorias = [...new Set(estado.productos.map((p) => p.categoria?.nombre).filter(Boolean))];

    dlgProductos.querySelector('[data-picker-categorias]').innerHTML = ['', ...categorias].map((n) => `
        <button type="button" class="chip" data-picker-cat="${escapar(n)}" aria-pressed="${n === pickerFiltro.categoria}">${escapar(n || 'Todas')}</button>`).join('');

    const t = pickerFiltro.texto.trim().toLowerCase();
    const visibles = estado.productos.filter((p) => (!pickerFiltro.categoria || p.categoria?.nombre === pickerFiltro.categoria)
        && (!t || `${p.nombre} ${p.descripcion ?? ''}`.toLowerCase().includes(t)));

    lista.innerHTML = visibles.length ? visibles.map((p) => {
        const enComanda = (c?.detalles ?? []).filter((d) => d.producto_id === p.id).reduce((n, d) => n + d.cantidad, 0);
        return `
            <div class="picker-item ${p.disponible ? '' : 'picker-item--agotado'}">
                <div>
                    <strong>${escapar(p.nombre)}</strong>
                    <span class="muted">${escapar(p.categoria?.nombre ?? '')}${enComanda ? ` · ya en la comanda: ${enComanda}` : ''}</span>
                </div>
                <span class="picker-precio">${colones(p.precio)}</span>
                <button type="button" class="btn ${p.disponible ? 'btn--primary' : ''}" data-picker-agregar="${p.id}" ${p.disponible ? '' : 'disabled'}>
                    ${p.disponible ? 'Añadir' : 'Agotado'}
                </button>
            </div>`;
    }).join('') : '<p class="empty-state">Ningún producto coincide con la búsqueda.</p>';

    dlgProductos.querySelector('[data-picker-resumen]').textContent = c
        ? `En la comanda: ${cantidadItems(c)} productos · ${colones(totalDe(c))}` : '';
}

async function agregarProducto(productoId, boton) {
    const c = comandaActual();
    const notaInput = dlgProductos.querySelector('[data-picker-nota]');
    const nota = notaInput.value.trim();
    mostrarErrorForm(dlgProductos, '');

    await conBoton(boton, async () => {
        try {
            // Mismo producto sin nota → se suma a la línea que ya existe, en vez de duplicarla.
            const existente = !nota && (c.detalles ?? []).find((d) => d.producto_id === productoId && !d.notas);
            if (existente) await api.put(`/detalles/${existente.id}`, { cantidad: existente.cantidad + 1 });
            else await api.post(`/comandas/${c.id}/detalles`, { producto_id: productoId, cantidad: 1, notas: nota || null });
            notaInput.value = '';
            await refrescar(c.id);
            pintarSelector();
        } catch (error) {
            mostrarErrorForm(dlgProductos, mensajeError(error));
            if (error.codigo === 'producto_no_disponible') {
                estado.productos = null; // se vuelve a pedir el menú con la disponibilidad actual
            }
        }
    });
}

dlgProductos.addEventListener('click', (e) => {
    const agregar = e.target.closest('[data-picker-agregar]');
    if (agregar) { agregarProducto(Number(agregar.dataset.pickerAgregar), agregar); return; }
    const cat = e.target.closest('[data-picker-cat]');
    if (cat) { pickerFiltro.categoria = cat.dataset.pickerCat; pintarSelector(); }
});
dlgProductos.querySelector('[data-picker-buscar]').addEventListener('input', (e) => {
    pickerFiltro.texto = e.target.value;
    if (estado.productos) pintarSelector();
});

/* ------------------------------------------------------------------ Cobro */

const dlgCobrar = document.getElementById('dlg-cobrar');
const formCobrar = dlgCobrar.querySelector('form');
let comandaACobrar = null;

function abrirCobro(comanda) {
    comandaACobrar = comanda;
    dlgCobrar.querySelector('[data-cobrar-sub]').textContent = `Mesa ${comanda.mesa?.numero} · comanda #${comanda.id}`;
    dlgCobrar.querySelector('[data-cobrar-totales]').innerHTML = `
        <div><span>Subtotal</span><span>${colones(comanda.subtotal)}</span></div>
        ${comanda.descuento > 0 ? `<div><span>Descuento (10 %)</span><span>− ${colones(comanda.descuento)}</span></div>` : ''}
        <div class="total"><span>Total a cobrar</span><strong>${colones(totalDe(comanda))}</strong></div>`;

    const grupo = dlgCobrar.querySelector('[data-cobrar-metodos]');
    grupo.innerHTML = `<legend>Método de pago</legend>${estado.metodos.map((m, i) => `
        <label class="metodo">
            <input type="radio" name="metodo" value="${m.id}" data-codigo="${escapar(m.codigo)}" ${i === 0 ? 'checked' : ''}>
            <span>${escapar(m.nombre)}</span>
        </label>`).join('')}`;

    dlgCobrar.querySelector('[data-cobrar-recibido]').value = '';
    actualizarEfectivo();
    mostrarErrorForm(dlgCobrar, '');
    dlgCobrar.showModal();
}

function actualizarEfectivo() {
    const elegido = formCobrar.querySelector('input[name="metodo"]:checked');
    const esEfectivo = elegido?.dataset.codigo === 'efectivo';
    dlgCobrar.querySelector('[data-cobrar-efectivo]').hidden = !esEfectivo;

    const recibido = Number(dlgCobrar.querySelector('[data-cobrar-recibido]').value);
    const total = totalDe(comandaACobrar ?? {});
    const hint = dlgCobrar.querySelector('[data-cobrar-vuelto]');
    if (!esEfectivo || !recibido) hint.textContent = '';
    else if (recibido < total) hint.textContent = `Faltan ${colones(total - recibido)}.`;
    else hint.textContent = `Vuelto: ${colones(recibido - total)}`;
}

formCobrar.addEventListener('change', actualizarEfectivo);
formCobrar.addEventListener('input', actualizarEfectivo);

formCobrar.addEventListener('submit', async (e) => {
    e.preventDefault();
    const elegido = formCobrar.querySelector('input[name="metodo"]:checked');
    if (!elegido) { mostrarErrorForm(dlgCobrar, 'Elegí el método de pago.'); return; }
    mostrarErrorForm(dlgCobrar, '');

    const comanda = comandaACobrar;
    await conBoton(formCobrar.querySelector('[type="submit"]'), async () => {
        try {
            const { data } = await api.post(`/comandas/${comanda.id}/cobrar`, { metodo_pago_id: Number(elegido.value) });
            dlgCobrar.close();
            avisar(`Cobro registrado: ${colones(data.total)} (${data.metodo_pago?.nombre ?? 'pago'}). La mesa ${comanda.mesa?.numero} quedó libre.`);
            estado.seleccionada = null;
            history.replaceState(null, '', window.location.pathname);
            await cargar({ silencioso: true });
        } catch (error) {
            mostrarErrorForm(dlgCobrar, mensajeError(error));
            if (error.codigo === 'comanda_ya_cerrada') cargar({ silencioso: true });
        }
    });
});

/* ------------------------------------------------------------------ Filtros y selección */

$lista.addEventListener('click', (e) => {
    const tarjeta = e.target.closest('[data-comanda]');
    if (tarjeta) seleccionar(Number(tarjeta.dataset.comanda), { desplazar: true });
});

document.querySelector('[data-tabs]').addEventListener('click', (e) => {
    const tab = e.target.closest('[data-tab]');
    if (!tab) return;
    estado.filtros.estado = tab.dataset.tab;
    reaplicarFiltros();
});

document.querySelector('[data-filtro-buscar]').addEventListener('input', (e) => {
    estado.filtros.texto = e.target.value.trim().toLowerCase();
    reaplicarFiltros();
});

/** Al filtrar, si la comanda abierta quedó oculta se muestra la primera visible. */
function reaplicarFiltros() {
    const visibles = comandasFiltradas();
    if (!visibles.some((c) => c.id === estado.seleccionada)) estado.seleccionada = visibles[0]?.id ?? null;
    pintarTabs();
    pintarLista();
    pintarDetalle();
}

window.addEventListener('hashchange', () => {
    const id = Number(window.location.hash.slice(1));
    if (id && estado.comandas.some((c) => c.id === id)) seleccionar(id);
});

/* ------------------------------------------------------------------ Arranque */

(async () => {
    await asegurarRol();
    await cargar();

    setInterval(() => {
        if (document.hidden || document.querySelector('dialog[open]')) return;
        cargar({ silencioso: true });
    }, ACTUALIZAR_CADA_MS);
})();
