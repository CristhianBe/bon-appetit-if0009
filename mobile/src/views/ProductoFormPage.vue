<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button :default-href="esEdicion ? `/productos/${id}` : '/productos'" text="" />
        </ion-buttons>
        <ion-title>{{ esEdicion ? 'Editar producto' : 'Nuevo producto' }}</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="contenido-seguro">
      <div class="contenedor">
        <!-- Carga de categorías (y del producto, si es edición) -->
        <ion-list v-if="cargando" aria-busy="true" aria-label="Cargando formulario">
          <ion-item v-for="n in 5" :key="n">
            <ion-label>
              <ion-skeleton-text :animated="true" style="width: 30%" />
              <ion-skeleton-text :animated="true" style="width: 80%; height: 20px" />
            </ion-label>
          </ion-item>
        </ion-list>

        <EstadoError
          v-else-if="errorCarga"
          :mensaje="errorCarga.mensaje"
          :sin-conexion="errorCarga.sinConexion"
          @reintentar="cargarDatos"
        />

        <EstadoVacio
          v-else-if="categorias.length === 0"
          titulo="No hay categorías"
          mensaje="Para crear un producto primero tiene que existir al menos una categoría en el sistema."
        >
          <ion-button @click="cargarDatos">
            <ion-icon slot="start" :icon="refreshOutline" />
            Volver a consultar
          </ion-button>
        </EstadoVacio>

        <form v-else novalidate @submit.prevent="guardar">
          <ion-list lines="full">
            <ion-item>
              <ion-input
                v-model="form.nombre"
                label="Nombre *"
                label-placement="floating"
                :maxlength="100"
                :counter="true"
                :class="claseCampo('nombre')"
                :error-text="errores.nombre"
              />
            </ion-item>

            <ion-item>
              <ion-select
                v-model="form.categoria_id"
                label="Categoría *"
                label-placement="floating"
                interface="action-sheet"
                cancel-text="Cancelar"
                :class="claseCampo('categoria_id')"
              >
                <ion-select-option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                  {{ categoria.nombre }}
                </ion-select-option>
              </ion-select>
            </ion-item>
            <ion-note v-if="errores.categoria_id" color="danger" class="error-select">
              {{ errores.categoria_id }}
            </ion-note>

            <ion-item>
              <ion-input
                v-model="form.precio"
                type="number"
                inputmode="decimal"
                label="Precio (₡) *"
                label-placement="floating"
                min="1"
                max="999999.99"
                step="0.01"
                :class="claseCampo('precio')"
                :error-text="errores.precio"
              />
            </ion-item>

            <ion-item>
              <ion-textarea
                v-model="form.descripcion"
                label="Descripción"
                label-placement="floating"
                :auto-grow="true"
                :maxlength="1000"
                :counter="true"
                :class="claseCampo('descripcion')"
                :error-text="errores.descripcion"
              />
            </ion-item>

            <ion-item>
              <ion-input
                v-model="form.imagen_url"
                type="url"
                inputmode="url"
                label="URL de la imagen"
                label-placement="floating"
                placeholder="https://..."
                :maxlength="255"
                :class="claseCampo('imagen_url')"
                :error-text="errores.imagen_url"
              />
            </ion-item>

            <ion-item>
              <ion-toggle v-model="form.disponible">Disponible para la venta</ion-toggle>
            </ion-item>
          </ion-list>

          <ion-card v-if="mensajeError" color="danger" role="alert">
            <ion-card-content>{{ mensajeError }}</ion-card-content>
          </ion-card>

          <ion-button type="submit" expand="block" class="ion-margin-top" :disabled="guardando">
            <ion-spinner v-if="guardando" name="crescent" />
            <template v-else>
              <ion-icon slot="start" :icon="saveOutline" />
              {{ esEdicion ? 'Guardar cambios' : 'Crear producto' }}
            </template>
          </ion-button>
        </form>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import {
  alertController,
  IonBackButton,
  IonButton,
  IonButtons,
  IonCard,
  IonCardContent,
  IonContent,
  IonHeader,
  IonIcon,
  IonInput,
  IonItem,
  IonLabel,
  IonList,
  IonNote,
  IonPage,
  IonSelect,
  IonSelectOption,
  IonSkeletonText,
  IonSpinner,
  IonTextarea,
  IonTitle,
  IonToggle,
  IonToolbar,
  toastController,
} from '@ionic/vue';
import { refreshOutline, saveOutline } from 'ionicons/icons';
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import EstadoError from '@/components/EstadoError.vue';
import EstadoVacio from '@/components/EstadoVacio.vue';
import { normalizarError, type ErrorNormalizado } from '@/services/errores';
import {
  asegurarPermisoNotificaciones,
  INSTRUCCIONES_HABILITAR,
  notificarProductoGuardado,
} from '@/services/notificaciones';
import {
  actualizarProducto,
  crearProducto,
  listadoDesactualizado,
  listarCategorias,
  obtenerProducto,
  type Categoria,
  type ProductoPayload,
} from '@/services/productosApi';

type Campo = 'nombre' | 'descripcion' | 'precio' | 'categoria_id' | 'imagen_url' | 'disponible';

const props = defineProps<{ id?: number }>();

const router = useRouter();
const esEdicion = computed(() => props.id !== undefined);

const categorias = ref<Categoria[]>([]);
const cargando = ref(true);
const errorCarga = ref<ErrorNormalizado | null>(null);
const guardando = ref(false);
const mensajeError = ref('');
const errores = reactive<Partial<Record<Campo, string>>>({});

const form = reactive<{
  nombre: string;
  descripcion: string;
  precio: string | number;
  categoria_id: number | null;
  disponible: boolean;
  imagen_url: string;
}>({
  nombre: '',
  descripcion: '',
  precio: '',
  categoria_id: null,
  disponible: true,
  imagen_url: '',
});

function claseCampo(campo: Campo): Record<string, boolean> {
  return { 'ion-invalid': !!errores[campo], 'ion-touched': !!errores[campo] };
}

async function cargarDatos(): Promise<void> {
  cargando.value = true;
  errorCarga.value = null;
  try {
    const [listaCategorias, producto] = await Promise.all([
      listarCategorias(),
      esEdicion.value ? obtenerProducto(props.id as number) : Promise.resolve(null),
    ]);
    categorias.value = listaCategorias;

    if (producto) {
      form.nombre = producto.nombre;
      form.descripcion = producto.descripcion ?? '';
      form.precio = Number(producto.precio);
      form.categoria_id = producto.categoria_id;
      form.disponible = producto.disponible;
      form.imagen_url = producto.imagen_url ?? '';
    }
  } catch (e) {
    errorCarga.value = normalizarError(e);
  } finally {
    cargando.value = false;
  }
}

/** Replica las reglas de ProductoStoreRequest/ProductoUpdateRequest (el backend valida igual). */
function validar(): boolean {
  (Object.keys(errores) as Campo[]).forEach((campo) => delete errores[campo]);

  const nombre = form.nombre.trim();
  if (!nombre) {
    errores.nombre = 'El nombre del producto es obligatorio.';
  } else if (nombre.length > 100) {
    errores.nombre = 'El nombre no puede superar los 100 caracteres.';
  }

  if (form.descripcion.length > 1000) {
    errores.descripcion = 'La descripción no puede superar los 1000 caracteres.';
  }

  const precio = Number(form.precio);
  if (form.precio === '' || form.precio === null) {
    errores.precio = 'El precio es obligatorio.';
  } else if (Number.isNaN(precio)) {
    errores.precio = 'El precio debe ser un número.';
  } else if (precio < 1) {
    errores.precio = 'El precio debe ser mayor a 0.';
  } else if (precio > 999999.99) {
    errores.precio = 'El precio ingresado es demasiado alto.';
  }

  if (!form.categoria_id) {
    errores.categoria_id = 'Debe indicar la categoría del producto.';
  }

  const imagen = form.imagen_url.trim();
  if (imagen) {
    try {
      const url = new URL(imagen);
      if (!['http:', 'https:'].includes(url.protocol)) {
        throw new Error('protocolo');
      }
    } catch {
      errores.imagen_url = 'La URL de la imagen no tiene un formato válido.';
    }
    if (imagen.length > 255) {
      errores.imagen_url = 'La URL de la imagen es demasiado larga.';
    }
  }

  return Object.keys(errores).length === 0;
}

function armarPayload(): ProductoPayload {
  return {
    nombre: form.nombre.trim(),
    descripcion: form.descripcion.trim() || null,
    precio: Number(form.precio),
    categoria_id: form.categoria_id as number,
    disponible: form.disponible,
    imagen_url: form.imagen_url.trim() || null,
  };
}

async function avisarConNotificacion(nombre: string, esNuevo: boolean): Promise<void> {
  const permiso = await asegurarPermisoNotificaciones();

  if (permiso === 'concedido') {
    await notificarProductoGuardado(nombre, esNuevo);
    return;
  }

  if (permiso === 'denegado-permanente') {
    const alerta = await alertController.create({
      header: 'Notificaciones desactivadas',
      message: INSTRUCCIONES_HABILITAR,
      buttons: ['Entendido'],
    });
    await alerta.present();
    await alerta.onDidDismiss();
    return;
  }

  if (permiso === 'denegado') {
    const toast = await toastController.create({
      message: 'No diste permiso para notificaciones; no se mostrará el aviso del sistema.',
      color: 'medium',
      duration: 3000,
    });
    await toast.present();
  }
  // 'no-soportado' (navegador): solo queda el toast de éxito.
}

async function guardar(): Promise<void> {
  mensajeError.value = '';
  if (!validar()) {
    return;
  }

  guardando.value = true;
  try {
    const payload = armarPayload();
    const producto = esEdicion.value
      ? await actualizarProducto(props.id as number, payload)
      : await crearProducto(payload);

    listadoDesactualizado.value = true;

    const toast = await toastController.create({
      message: esEdicion.value ? 'Cambios guardados.' : 'Producto creado.',
      color: 'success',
      duration: 2000,
    });
    await toast.present();

    await avisarConNotificacion(producto.nombre, !esEdicion.value);

    if (esEdicion.value) {
      router.back();
    } else {
      // replace: el formulario de "nuevo" no queda en el historial detrás del detalle.
      await router.replace({ name: 'producto-detalle', params: { id: producto.id } });
    }
  } catch (e) {
    const normalizado = normalizarError(e);
    // 422 validacion_fallida -> errores por campo del FormRequest.
    for (const [campo, mensaje] of Object.entries(normalizado.errores)) {
      errores[campo as Campo] = mensaje;
    }
    mensajeError.value = normalizado.mensaje;
  } finally {
    guardando.value = false;
  }
}

onMounted(cargarDatos);
</script>

<style scoped>
.error-select {
  display: block;
  font-size: 12px;
  padding: 4px 16px 8px;
}
</style>
