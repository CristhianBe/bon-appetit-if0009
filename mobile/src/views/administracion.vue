<template>
  <ion-page>
    <AppHeader titulo="Administración" />

    <ion-content class="contenido-seguro">
      <ion-refresher slot="fixed" @ion-refresh="refrescar">
        <ion-refresher-content />
      </ion-refresher>

      <div class="contenedor-ancho">
        <!-- Solo el administrador gestiona categorías y usuarios (la API responde 403 al resto). -->
        <EstadoVacio
          v-if="!esAdmin"
          titulo="Solo para administradores"
          mensaje="Tu rol no tiene acceso a esta sección."
        />

        <template v-else>
          <ion-segment v-model="tab" class="tabs">
            <ion-segment-button value="categorias"><ion-label>Categorías</ion-label></ion-segment-button>
            <ion-segment-button value="usuarios"><ion-label>Usuarios</ion-label></ion-segment-button>
          </ion-segment>

          <div v-if="cargando && !hayDatos" class="estado-centrado"><ion-spinner /></div>
          <EstadoError v-else-if="error && !hayDatos" :mensaje="error.mensaje" :sin-conexion="error.sinConexion" @reintentar="cargar" />

          <!-- Categorías -->
          <ion-list v-else-if="tab === 'categorias'" lines="full" class="lista">
            <ion-item-sliding v-for="c in categorias" :key="c.id">
              <ion-item>
                <ion-label>
                  <h2>{{ c.nombre }}</h2>
                  <p v-if="c.descripcion">{{ c.descripcion }}</p>
                </ion-label>
              </ion-item>
              <ion-item-options side="end">
                <ion-item-option color="tertiary" @click="editarCategoria(c)">
                  <ion-icon slot="icon-only" :icon="pencil" aria-label="Editar" />
                </ion-item-option>
                <ion-item-option color="danger" @click="eliminarCategoria(c)">
                  <ion-icon slot="icon-only" :icon="trash" aria-label="Eliminar" />
                </ion-item-option>
              </ion-item-options>
            </ion-item-sliding>
            <p class="ayuda">Deslizá una fila hacia la izquierda para editarla o eliminarla.</p>
          </ion-list>

          <!-- Usuarios -->
          <ion-list v-else lines="full" class="lista">
            <ion-item-sliding v-for="u in usuarios" :key="u.id">
              <ion-item>
                <ion-label>
                  <h2>{{ u.name }}<span v-if="u.id === usuarioActual?.id" class="yo"> (vos)</span></h2>
                  <p>{{ u.email }}</p>
                </ion-label>
                <ion-badge slot="end" :color="COLOR_ROL[u.rol ?? ''] ?? 'medium'">{{ u.rol ? ETIQUETA_ROL[u.rol] : 'Sin rol' }}</ion-badge>
              </ion-item>
              <ion-item-options side="end">
                <ion-item-option color="tertiary" @click="editarUsuario(u)">
                  <ion-icon slot="icon-only" :icon="pencil" aria-label="Editar" />
                </ion-item-option>
                <ion-item-option v-if="u.id !== usuarioActual?.id" color="danger" @click="eliminarUsuario(u)">
                  <ion-icon slot="icon-only" :icon="trash" aria-label="Eliminar" />
                </ion-item-option>
              </ion-item-options>
            </ion-item-sliding>
            <p class="ayuda">Deslizá una fila hacia la izquierda para editarla o eliminarla.</p>
          </ion-list>
        </template>
      </div>

      <ion-fab v-if="esAdmin" slot="fixed" vertical="bottom" horizontal="end">
        <ion-fab-button :aria-label="tab === 'categorias' ? 'Nueva categoría' : 'Nuevo usuario'" @click="tab === 'categorias' ? editarCategoria() : editarUsuario()">
          <ion-icon :icon="add" />
        </ion-fab-button>
      </ion-fab>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import {
  alertController, IonBadge, IonContent, IonFab, IonFabButton, IonIcon, IonItem, IonItemOption, IonItemOptions,
  IonItemSliding, IonLabel, IonList, IonPage, IonRefresher, IonRefresherContent, IonSegment, IonSegmentButton,
  IonSpinner, onIonViewWillEnter, toastController,
} from '@ionic/vue';
import { add, pencil, trash } from 'ionicons/icons';
import { computed, ref } from 'vue';
import AppHeader from '@/components/AppHeader.vue';
import EstadoError from '@/components/EstadoError.vue';
import EstadoVacio from '@/components/EstadoVacio.vue';
import { normalizarError, type ErrorNormalizado } from '@/services/errores';
import { http } from '@/services/http';
import type { Categoria } from '@/services/productosApi';
import { traerTodo } from '@/services/salonApi';
import { ETIQUETA_ROL, tieneRol, usuarioActual, type Rol } from '@/services/sesion';

interface UsuarioApi {
  id: number;
  name: string;
  email: string;
  rol: Rol | null;
}

const COLOR_ROL: Record<string, string> = { administrador: 'danger', mesero: 'tertiary', cajero: 'success' };

const esAdmin = computed(() => tieneRol('administrador'));
const tab = ref<'categorias' | 'usuarios'>('categorias');
const categorias = ref<Categoria[]>([]);
const usuarios = ref<UsuarioApi[]>([]);
const cargando = ref(false);
const error = ref<ErrorNormalizado | null>(null);
const hayDatos = computed(() => categorias.value.length > 0 || usuarios.value.length > 0);

async function cargar(): Promise<void> {
  if (!esAdmin.value) return;
  cargando.value = true;
  try {
    [categorias.value, usuarios.value] = await Promise.all([
      traerTodo<Categoria>('/categorias', { por_pagina: 50 }),
      traerTodo<UsuarioApi>('/usuarios', { por_pagina: 100 }),
    ]);
    error.value = null;
  } catch (e) {
    error.value = normalizarError(e);
  } finally {
    cargando.value = false;
  }
}

async function refrescar(evento: CustomEvent): Promise<void> {
  await cargar();
  (evento.target as HTMLIonRefresherElement).complete();
}

/** Ejecuta la llamada y muestra el mensaje del servidor (422/409) si falla. Devuelve true si salió bien. */
async function intentar(accion: () => Promise<unknown>, exito: string): Promise<boolean> {
  try {
    await accion();
    await avisar(exito);
    await cargar();
    return true;
  } catch (e) {
    const err = normalizarError(e);
    await avisar(Object.values(err.errores)[0] ?? err.mensaje);
    return false;
  }
}

async function confirmar(titulo: string, mensaje: string): Promise<boolean> {
  const alerta = await alertController.create({
    header: titulo,
    message: mensaje,
    buttons: [{ text: 'Cancelar', role: 'cancel' }, { text: 'Eliminar', role: 'destructive' }],
  });
  await alerta.present();
  return (await alerta.onDidDismiss()).role === 'destructive';
}

/* ---------------------------------------------------------------- Categorías */

async function editarCategoria(c?: Categoria): Promise<void> {
  const alerta = await alertController.create({
    header: c ? 'Editar categoría' : 'Nueva categoría',
    inputs: [
      { name: 'nombre', type: 'text', value: c?.nombre ?? '', placeholder: 'Nombre (ej.: Postres)', attributes: { maxlength: 100, 'aria-label': 'Nombre' } },
      { name: 'descripcion', type: 'textarea', value: c?.descripcion ?? '', placeholder: 'Descripción (opcional)', attributes: { 'aria-label': 'Descripción' } },
    ],
    buttons: [{ text: 'Cancelar', role: 'cancel' }, { text: 'Guardar', role: 'confirm' }],
  });
  await alerta.present();
  const { role, data } = await alerta.onDidDismiss();
  if (role !== 'confirm') return;

  const datos = { nombre: String(data.values.nombre ?? '').trim(), descripcion: String(data.values.descripcion ?? '').trim() || null };
  if (!datos.nombre) { await avisar('El nombre de la categoría es obligatorio.'); return; }
  await intentar(
    () => (c ? http.put(`/categorias/${c.id}`, datos) : http.post('/categorias', datos)),
    c ? `Categoría «${datos.nombre}» guardada.` : `Categoría «${datos.nombre}» creada.`,
  );
}

async function eliminarCategoria(c: Categoria): Promise<void> {
  if (!(await confirmar(`Eliminar «${c.nombre}»`, 'Solo se puede eliminar si no tiene platillos asociados.'))) return;
  await intentar(() => http.delete(`/categorias/${c.id}`), `Categoría «${c.nombre}» eliminada.`);
}

/* ---------------------------------------------------------------- Usuarios */

async function editarUsuario(u?: UsuarioApi): Promise<void> {
  const datos = await alertController.create({
    header: u ? `Editar a ${u.name}` : 'Nuevo usuario',
    inputs: [
      { name: 'name', type: 'text', value: u?.name ?? '', placeholder: 'Nombre completo', attributes: { 'aria-label': 'Nombre' } },
      { name: 'email', type: 'email', value: u?.email ?? '', placeholder: 'Correo electrónico', attributes: { 'aria-label': 'Correo' } },
      { name: 'password', type: 'password', placeholder: u ? 'Contraseña nueva (opcional)' : 'Contraseña (mín. 8)', attributes: { minlength: 8, 'aria-label': 'Contraseña' } },
    ],
    buttons: [{ text: 'Cancelar', role: 'cancel' }, { text: 'Siguiente', role: 'confirm' }],
  });
  await datos.present();
  const paso1 = await datos.onDidDismiss();
  if (paso1.role !== 'confirm') return;

  const { name, email, password } = paso1.data.values as Record<string, string>;
  if (!name?.trim() || !email?.trim() || (!u && (password ?? '').length < 8)) {
    await avisar('Completá nombre, correo y una contraseña de al menos 8 caracteres.');
    return;
  }

  // El administrador no se cambia su propio rol (para no quedarse sin acceso).
  let rol: Rol | null = u?.rol ?? 'mesero';
  if (!u || u.id !== usuarioActual.value?.id) {
    const elegir = await alertController.create({
      header: 'Rol',
      inputs: (['administrador', 'mesero', 'cajero'] as Rol[]).map((r) => ({ type: 'radio' as const, label: ETIQUETA_ROL[r], value: r, checked: r === rol })),
      buttons: [{ text: 'Cancelar', role: 'cancel' }, { text: 'Guardar', role: 'confirm' }],
    });
    await elegir.present();
    const paso2 = await elegir.onDidDismiss();
    if (paso2.role !== 'confirm') return;
    rol = paso2.data.values as Rol;
  }

  const cuerpo: Record<string, unknown> = { name: name.trim(), email: email.trim(), rol };
  if (password) cuerpo.password = password;
  await intentar(
    () => (u ? http.put(`/usuarios/${u.id}`, cuerpo) : http.post('/usuarios', cuerpo)),
    u ? `Usuario ${cuerpo.name} actualizado.` : `Usuario ${cuerpo.name} creado como ${ETIQUETA_ROL[rol!]}.`,
  );
}

async function eliminarUsuario(u: UsuarioApi): Promise<void> {
  if (!(await confirmar(`Eliminar a ${u.name}`, 'Pierde el acceso. No se puede eliminar si ya registró comandas o reservas.'))) return;
  await intentar(() => http.delete(`/usuarios/${u.id}`), `Usuario ${u.name} eliminado.`);
}

async function avisar(mensaje: string): Promise<void> {
  const toast = await toastController.create({ message: mensaje, duration: 2600, position: 'bottom', positionAnchor: 'barra-inferior' });
  await toast.present();
}

onIonViewWillEnter(cargar);
</script>

<style scoped>
.contenedor-ancho { max-width: 900px; margin: 0 auto; }
.tabs { margin-bottom: 12px; }
.lista { border-radius: 12px; overflow: hidden; border: 1px solid var(--ba-borde); padding: 0; }
.lista h2 { font-weight: 600; font-size: 15px; }
.yo { color: var(--ion-color-medium); font-weight: 400; }
.ayuda { margin: 0; padding: 10px 14px; font-size: 12px; color: var(--ion-color-medium); background: #fff; }
</style>
