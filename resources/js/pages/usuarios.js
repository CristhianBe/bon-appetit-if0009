import {
    ETIQUETA_ROL, api, asegurarRol, avisar, confirmar, conBoton, escapar, mensajeError, mostrarErrorForm,
    pintarCargando, pintarError, tieneRol, traerTodo, usuario,
} from '../core.js';

const estado = { usuarios: [], roles: [], texto: '' };

const $contenido = document.querySelector('[data-contenido-usuarios]');
const $lista = document.querySelector('[data-lista-usuarios]');
const dialogo = document.getElementById('dlg-usuario');
const form = dialogo.querySelector('form');
let editando = null;

const TONO_ROL = { administrador: 'danger', mesero: 'info', cajero: 'success' };
const esYo = (u) => u.id === usuario()?.id;

function pintarRoles() {
    document.querySelector('[data-roles-resumen]').innerHTML = estado.roles.map((r) => {
        const cuantos = estado.usuarios.filter((u) => u.rol === r.nombre).length;
        return `
            <article class="panel-card rol-card">
                <span class="badge badge--${TONO_ROL[r.nombre] ?? 'neutral'}">${escapar(ETIQUETA_ROL[r.nombre] ?? r.nombre)}</span>
                <p>${escapar(r.descripcion ?? '')}</p>
                <strong>${cuantos} ${cuantos === 1 ? 'persona' : 'personas'}</strong>
            </article>`;
    }).join('');
}

function pintarTabla() {
    const t = estado.texto;
    const visibles = estado.usuarios.filter((u) => !t || `${u.name} ${u.email}`.toLowerCase().includes(t));

    if (visibles.length === 0) {
        $lista.innerHTML = '<p class="empty-state">No hay usuarios que coincidan con la búsqueda.</p>';
        return;
    }

    $lista.innerHTML = `
        <div class="tabla-scroll">
            <table class="tabla">
                <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th class="tabla-acciones"><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>${visibles.map((u) => `
                    <tr>
                        <td><strong>${escapar(u.name)}</strong>${esYo(u) ? ' <span class="muted">(vos)</span>' : ''}</td>
                        <td>${escapar(u.email)}</td>
                        <td><span class="badge badge--${TONO_ROL[u.rol] ?? 'neutral'}">${escapar(ETIQUETA_ROL[u.rol] ?? 'Sin rol')}</span></td>
                        <td class="tabla-acciones">
                            <button type="button" class="btn btn--outline" data-u-editar="${u.id}">Editar</button>
                            ${esYo(u) ? '' : `<button type="button" class="btn btn--ghost btn--peligro" data-u-eliminar="${u.id}">Eliminar</button>`}
                        </td>
                    </tr>`).join('')}
                </tbody>
            </table>
        </div>`;
}

async function cargar() {
    pintarCargando($lista, 'Cargando usuarios…');
    try {
        const [usuarios, roles] = await Promise.all([traerTodo('/usuarios?por_pagina=100'), traerTodo('/roles')]);
        estado.usuarios = usuarios;
        estado.roles = roles;
        pintarRoles();
        pintarTabla();
    } catch (error) {
        pintarError($lista, mensajeError(error), cargar);
    }
}

function actualizarHintRol() {
    const rol = estado.roles.find((r) => r.nombre === form.rol.value);
    dialogo.querySelector('[data-rol-hint]').textContent = rol?.descripcion ?? '';
}

function abrirFormulario(u = null) {
    editando = u;
    dialogo.querySelector('[data-usuario-titulo]').textContent = u ? `Editar a ${u.name}` : 'Nuevo usuario';
    form.rol.innerHTML = estado.roles.map((r) => `<option value="${escapar(r.nombre)}">${escapar(ETIQUETA_ROL[r.nombre] ?? r.nombre)}</option>`).join('');
    form.nombre.value = u?.name ?? '';
    form.email.value = u?.email ?? '';
    form.rol.value = u?.rol ?? 'mesero';
    form.rol.disabled = Boolean(u && esYo(u));
    form.password.value = '';
    form.password.required = !u;
    dialogo.querySelector('[data-password-etiqueta]').textContent = u ? 'Contraseña nueva (opcional)' : 'Contraseña';
    dialogo.querySelector('[data-password-hint]').textContent = u
        ? 'Dejala vacía para conservar la contraseña actual. Mínimo 8 caracteres.'
        : 'Mínimo 8 caracteres. Compartila con la persona por un canal seguro.';
    actualizarHintRol();
    mostrarErrorForm(dialogo, '');
    dialogo.showModal();
    form.nombre.focus();
}

form.rol.addEventListener('change', actualizarHintRol);

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!form.checkValidity()) {
        mostrarErrorForm(dialogo, 'Revisá el nombre, el correo y la contraseña (mínimo 8 caracteres).');
        form.querySelector(':invalid')?.focus();
        return;
    }
    mostrarErrorForm(dialogo, '');

    const datos = { name: form.nombre.value.trim(), email: form.email.value.trim(), rol: form.rol.value };
    if (form.password.value) datos.password = form.password.value;

    await conBoton(form.querySelector('[type="submit"]'), async () => {
        try {
            if (editando) await api.put(`/usuarios/${editando.id}`, datos);
            else await api.post('/usuarios', datos);
            dialogo.close();
            avisar(editando ? `Usuario ${datos.name} actualizado.` : `Usuario ${datos.name} creado como ${ETIQUETA_ROL[datos.rol] ?? datos.rol}.`);
            await cargar();
        } catch (error) {
            mostrarErrorForm(dialogo, mensajeError(error));
        }
    });
});

$lista.addEventListener('click', async (e) => {
    const boton = e.target.closest('button');
    if (!boton) return;
    const id = boton.dataset.uEditar ?? boton.dataset.uEliminar;
    const u = estado.usuarios.find((x) => String(x.id) === id);
    if (!u) return;

    if (boton.dataset.uEditar) { abrirFormulario(u); return; }

    const acepta = await confirmar({
        titulo: `Eliminar a ${u.name}`,
        mensaje: 'Pierde el acceso al sistema. Si ya registró comandas o reservas no se puede eliminar.',
        aceptar: 'Eliminar', peligro: true,
    });
    if (!acepta) return;
    try {
        await api.delete(`/usuarios/${u.id}`);
        avisar(`Usuario ${u.name} eliminado.`);
        await cargar();
    } catch (error) {
        avisar(mensajeError(error));
    }
});

document.querySelector('[data-nuevo-usuario]').addEventListener('click', () => abrirFormulario());
document.querySelector('[data-buscar-usuario]').addEventListener('input', (e) => {
    estado.texto = e.target.value.trim().toLowerCase();
    pintarTabla();
});

(async () => {
    await asegurarRol();
    if (!tieneRol('administrador')) {
        document.querySelector('[data-sin-permiso]').hidden = false;
        return;
    }
    $contenido.hidden = false;
    await cargar();
})();
