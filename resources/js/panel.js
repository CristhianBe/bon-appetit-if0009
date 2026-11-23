/**
 * Comportamiento compartido del panel web: sesión, filtros, diálogos, comandas,
 * formulario de reservas y login. Cada parte se activa solo si su HTML existe en la página.
 */

const CLAVE_SESION = 'ba_sesion';
const DURACION_TOKEN_MS = 30 * 60 * 1000; // igual que la expiración del token en AuthController

/* ----------------------------------------------------------------- Sesión */
function leerSesion() {
    for (const almacen of [sessionStorage, localStorage]) {
        try {
            const sesion = JSON.parse(almacen.getItem(CLAVE_SESION) || 'null');
            if (sesion) return sesion;
        } catch {
            /* almacenamiento bloqueado o dato corrupto */
        }
    }
    return null;
}

function guardarSesion(sesion, recordar) {
    try {
        (recordar ? localStorage : sessionStorage).setItem(CLAVE_SESION, JSON.stringify(sesion));
    } catch {
        /* sin almacenamiento: la sesión dura solo esta carga de página */
    }
}

function borrarSesion() {
    for (const almacen of [sessionStorage, localStorage]) {
        try { almacen.removeItem(CLAVE_SESION); } catch { /* nada */ }
    }
}

function sesionVigente() {
    const sesion = leerSesion();
    if (!sesion?.token || Date.now() > sesion.expira) {
        borrarSesion();
        return null;
    }
    return sesion;
}

function iniciales(nombre = '') {
    return nombre.split(/\s+/).filter(Boolean).slice(0, 2).map((p) => p[0].toUpperCase()).join('') || 'BA';
}

function protegerPagina() {
    const sesion = sesionVigente();
    if (!sesion) {
        window.location.replace('/login');
        return;
    }
    document.querySelectorAll('[data-user-name]').forEach((el) => { el.textContent = sesion.user?.name ?? 'Usuario'; });
    document.querySelectorAll('[data-user-initials]').forEach((el) => { el.textContent = iniciales(sesion.user?.name); });

    document.querySelectorAll('[data-logout]').forEach((boton) => {
        boton.addEventListener('click', async () => {
            boton.disabled = true;
            try {
                await fetch('/api/logout', {
                    method: 'POST',
                    headers: { Accept: 'application/json', Authorization: `Bearer ${sesion.token}` },
                });
            } catch {
                /* aunque falle la red, la sesión local se cierra igual */
            }
            borrarSesion();
            window.location.replace('/login');
        });
    });
}

/* ----------------------------------------------------------------- Aviso flotante */
let temporizadorAviso;
export function avisar(texto) {
    const toast = document.querySelector('[data-toast]');
    if (!toast) return;
    toast.querySelector('[data-toast-text]').textContent = texto;
    toast.classList.add('is-visible');
    clearTimeout(temporizadorAviso);
    temporizadorAviso = setTimeout(() => toast.classList.remove('is-visible'), 3200);
}

/* ----------------------------------------------------------------- Diálogos y acciones demo */
function iniciarDialogos() {
    document.addEventListener('click', (e) => {
        const abrir = e.target.closest('[data-abrir]');
        if (abrir) document.getElementById(abrir.dataset.abrir)?.showModal();

        const cerrar = e.target.closest('[data-cerrar]');
        if (cerrar) cerrar.closest('dialog')?.close();

        const demo = e.target.closest('[data-demo]');
        if (demo) avisar(demo.dataset.demo);
    });

    // Clic en el fondo oscuro cierra el diálogo.
    document.querySelectorAll('dialog').forEach((dialogo) => {
        dialogo.addEventListener('click', (e) => { if (e.target === dialogo) dialogo.close(); });
    });
}

/* ----------------------------------------------------------------- Filtros (mesas y comandas) */
function iniciarFiltros() {
    const barra = document.querySelector('[data-filtros]');
    if (!barra) return;

    const items = [...document.querySelectorAll('[data-item]')];
    const vacio = document.querySelector('[data-vacio]');
    const filtros = {};
    let texto = '';

    const aplicar = () => {
        let visibles = 0;
        for (const item of items) {
            const coincide = Object.entries(filtros).every(([clave, valor]) => !valor || item.dataset[clave] === valor)
                && (!texto || item.dataset.texto.includes(texto));
            item.hidden = !coincide;
            if (coincide) visibles += 1;
        }
        if (vacio) vacio.hidden = visibles > 0;
        barra.dispatchEvent(new CustomEvent('filtrado'));
    };

    barra.querySelectorAll('[data-filtro]').forEach((chip) => {
        chip.addEventListener('click', () => {
            const grupo = chip.dataset.filtro;
            barra.querySelectorAll(`[data-filtro="${grupo}"]`).forEach((c) => c.setAttribute('aria-pressed', String(c === chip)));
            filtros[grupo] = chip.dataset.valor;
            aplicar();
        });
    });

    barra.querySelectorAll('[data-filtro-select]').forEach((select) => {
        select.addEventListener('change', () => { filtros[select.dataset.filtroSelect] = select.value; aplicar(); });
    });

    barra.querySelector('[data-filtro-buscar]')?.addEventListener('input', (e) => {
        texto = e.target.value.trim().toLowerCase();
        aplicar();
    });
}

/* ----------------------------------------------------------------- Comandas: lista + detalle */
function iniciarComandas() {
    const tarjetas = [...document.querySelectorAll('[data-comanda]')];
    if (!tarjetas.length) return;

    const seleccionar = (codigo, enfocar = false) => {
        tarjetas.forEach((t) => t.setAttribute('aria-current', String(t.dataset.comanda === codigo)));
        document.querySelectorAll('[data-detalle]').forEach((d) => { d.hidden = d.dataset.detalle !== codigo; });
        if (enfocar && window.matchMedia('(max-width: 1100px)').matches) {
            const detalle = document.querySelector(`[data-detalle="${codigo}"]`);
            detalle?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            detalle?.focus({ preventScroll: true });
        }
    };

    tarjetas.forEach((t) => t.addEventListener('click', () => {
        seleccionar(t.dataset.comanda, true);
        history.replaceState(null, '', `#${t.dataset.comanda}`);
    }));

    // Al filtrar, si la comanda abierta quedó oculta, se muestra la primera visible.
    document.querySelector('[data-filtros]')?.addEventListener('filtrado', () => {
        const actual = tarjetas.find((t) => t.getAttribute('aria-current') === 'true');
        if (actual && !actual.hidden) return;
        const primera = tarjetas.find((t) => !t.hidden);
        if (primera) seleccionar(primera.dataset.comanda);
    });

    // Enlace directo desde Mesas: /pedidos#C-0142
    const desdeUrl = decodeURIComponent(window.location.hash.slice(1));
    if (tarjetas.some((t) => t.dataset.comanda === desdeUrl)) seleccionar(desdeUrl);
}

/* ----------------------------------------------------------------- Reservas */
function iniciarReservas() {
    const form = document.querySelector('[data-form-reserva]');
    if (!form) return;

    const fecha = form.querySelector('#fecha');
    const hoy = new Date();
    const iso = new Date(hoy.getTime() - hoy.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
    fecha.min = iso;
    if (!fecha.value) fecha.value = iso;

    const error = form.querySelector('[data-form-error]');
    const lista = document.querySelector('[data-lista-reservas]');

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!form.checkValidity()) {
            const invalido = form.querySelector(':invalid');
            error.textContent = `Revisá el campo «${form.querySelector(`label[for="${invalido.id}"]`)?.firstChild.textContent.trim() ?? invalido.name}».`;
            error.hidden = false;
            invalido.focus();
            return;
        }
        error.hidden = true;

        const datos = new FormData(form);
        const item = document.createElement('li');
        item.className = 'reserva';
        const dia = datos.get('fecha') === iso ? 'Hoy' : datos.get('fecha').split('-').reverse().slice(0, 2).join('/');
        // textContent (no innerHTML): lo que escribe el usuario nunca se interpreta como HTML.
        item.innerHTML = '<span class="reserva-hora"><strong></strong><small></small></span>'
            + '<span class="reserva-info"><strong></strong><span></span></span>'
            + '<span class="badge badge--warning">Pendiente</span>';
        item.querySelector('.reserva-hora strong').textContent = datos.get('hora');
        item.querySelector('.reserva-hora small').textContent = dia;
        item.querySelector('.reserva-info strong').textContent = datos.get('nombre');
        item.querySelector('.reserva-info span').textContent = `${datos.get('personas')} personas · ${datos.get('zona')}`;
        lista.prepend(item);

        avisar(`Reserva registrada para ${datos.get('nombre')} (demostración, no se guarda en la base de datos)`);
        form.reset();
        fecha.value = iso;
    });
}

/* ----------------------------------------------------------------- Login */
function iniciarLogin() {
    const form = document.querySelector('[data-form-login]');
    if (!form) return;

    if (sesionVigente()) {
        window.location.replace('/mesas');
        return;
    }

    const error = form.querySelector('[data-login-error]');
    const boton = form.querySelector('button[type="submit"]');
    const password = form.querySelector('#password');

    form.querySelector('[data-ver-password]').addEventListener('click', (e) => {
        const visible = password.type === 'text';
        password.type = visible ? 'password' : 'text';
        e.currentTarget.setAttribute('aria-pressed', String(!visible));
        e.currentTarget.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    const mostrarError = (texto) => { error.textContent = texto; error.hidden = false; };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        error.hidden = true;
        if (!form.checkValidity()) {
            mostrarError('Ingresá un correo válido y tu contraseña.');
            form.querySelector(':invalid')?.focus();
            return;
        }

        boton.disabled = true;
        boton.textContent = 'Ingresando…';
        try {
            const respuesta = await fetch('/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ email: form.email.value.trim(), password: password.value }),
            });
            const cuerpo = await respuesta.json().catch(() => ({}));

            if (respuesta.ok && cuerpo.token) {
                guardarSesion({ token: cuerpo.token, user: cuerpo.user, expira: Date.now() + DURACION_TOKEN_MS },
                    form.querySelector('[data-recordar]').checked);
                window.location.replace('/mesas');
                return;
            }
            if (respuesta.status === 401) mostrarError('Correo o contraseña incorrectos.');
            else if (respuesta.status === 429) mostrarError('Demasiados intentos. Esperá un minuto e intentá de nuevo.');
            else if (respuesta.status === 422) mostrarError(Object.values(cuerpo.errors ?? {}).flat()[0] ?? 'Revisá los datos ingresados.');
            else mostrarError('No se pudo iniciar sesión. Intentá de nuevo en unos segundos.');
        } catch {
            mostrarError('Sin conexión con el servidor. Revisá tu red e intentá de nuevo.');
        }
        boton.disabled = false;
        boton.textContent = 'Iniciar sesión';
        password.select();
    });
}

/* ----------------------------------------------------------------- Arranque */
document.addEventListener('DOMContentLoaded', () => {
    if (document.body.dataset.page === 'login') {
        iniciarLogin();
        return;
    }
    protegerPagina();
    iniciarDialogos();
    iniciarFiltros();
    iniciarComandas();
    iniciarReservas();
});
