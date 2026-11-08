<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
         <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>Menú</ion-title>
        <ion-buttons slot="end">
          <ion-button aria-label="Cerrar sesión" @click="confirmarLogout">
            <ion-icon slot="icon-only" :icon="logOutOutline" />
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content class="contenido-seguro">
      <!-- Recarga por gesto de arrastre -->
      <ion-refresher slot="fixed" :disabled="cargandoInicial" @ion-refresh="alArrastrar">
        <ion-refresher-content pulling-text="Deslizá para actualizar" refreshing-spinner="crescent" />
      </ion-refresher>

      <div class="contenedor-ancho">
        <ion-text v-if="usuarioActual" color="medium">
          <p class="saludo">Hola, {{ usuarioActual.name }}</p>
        </ion-text>

        <AvisoDesactualizado v-if="actualizadoEnCache" :actualizado-en="actualizadoEnCache" />

        <!-- Carga inicial: skeleton -->
        <ion-list v-if="cargandoInicial" aria-busy="true" aria-label="Cargando productos">
          <ion-item v-for="n in 6" :key="n">
            <ion-thumbnail slot="start">
              <ion-skeleton-text :animated="true" />
            </ion-thumbnail>
            <ion-label>
              <h3><ion-skeleton-text :animated="true" style="width: 60%" /></h3>
              <p><ion-skeleton-text :animated="true" style="width: 40%" /></p>
            </ion-label>
          </ion-item>
        </ion-list>

        <!-- Error sin datos que mostrar -->
        <EstadoError
          v-else-if="error"
          :mensaje="error.mensaje"
          :sin-conexion="error.sinConexion"
          @reintentar="cargarInicial"
        />

        <!-- Vacío -->
        <EstadoVacio
          v-else-if="productos.length === 0"
          titulo="No hay productos en el menú"
          mensaje="Todavía no se registró ningún producto."
          :icono="restaurantOutline"
        >
          <ion-button @click="cargarInicial">
            <ion-icon slot="start" :icon="refreshOutline" />
            Volver a consultar
          </ion-button>
        </EstadoVacio>

        <!-- Listado -->
        <ion-grid v-else class="ion-no-padding">
          <ion-row>
            <ion-col v-for="producto in productos" :key="producto.id" size="12" size-md="6" size-xl="4">
              <ion-card button class="tarjeta" @click="abrirDetalle(producto.id)">
                <ion-item lines="none">
                  <ion-thumbnail slot="start">
                    <img v-if="producto.imagen_url" :src="producto.imagen_url" :alt="producto.nombre" />
                    <div v-else class="sin-imagen">
                      <ion-icon :icon="fastFoodOutline" color="medium" aria-hidden="true" />
                    </div>
                  </ion-thumbnail>
                  <ion-label class="ion-text-wrap">
                    <h2>{{ producto.nombre }}</h2>
                    <p>{{ producto.categoria?.nombre ?? 'Sin categoría' }}</p>
                    <ion-text color="primary">
                      <strong>{{ formatearPrecio(producto.precio) }}</strong>
                    </ion-text>
                  </ion-label>
                  <ion-badge slot="end" :color="producto.disponible ? 'success' : 'medium'">
                    {{ producto.disponible ? 'Disponible' : 'Agotado' }}
                  </ion-badge>
                </ion-item>
              </ion-card>
            </ion-col>
          </ion-row>
        </ion-grid>
      </div>

      <!-- Scroll infinito: pide la página siguiente al llegar al final -->
      <ion-infinite-scroll :disabled="!hayMasPaginas" threshold="150px" @ion-infinite="cargarSiguiente">
        <ion-infinite-scroll-content loading-spinner="crescent" loading-text="Cargando más productos..." />
      </ion-infinite-scroll>

      <ion-fab slot="fixed" vertical="bottom" horizontal="end">
        <ion-fab-button aria-label="Nuevo producto" @click="router.push({ name: 'producto-nuevo' })">
          <ion-icon :icon="add" />
        </ion-fab-button>
      </ion-fab>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import type { PluginListenerHandle } from '@capacitor/core';
import { Network } from '@capacitor/network';
import {
  alertController,
  IonBadge,
  IonButton,
  IonButtons,
  IonMenuButton,
  IonCard,
  IonCol,
  IonContent,
  IonFab,
  IonFabButton,
  IonGrid,
  IonHeader,
  IonIcon,
  IonInfiniteScroll,
  IonInfiniteScrollContent,
  IonItem,
  IonLabel,
  IonList,
  IonPage,
  IonRefresher,
  IonRefresherContent,
  IonRow,
  IonSkeletonText,
  IonText,
  IonThumbnail,
  IonTitle,
  IonToolbar,
  onIonViewWillEnter,
  toastController,
  type InfiniteScrollCustomEvent,
  type RefresherCustomEvent,
} from '@ionic/vue';
import { add, fastFoodOutline, logOutOutline, refreshOutline, restaurantOutline } from 'ionicons/icons';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AvisoDesactualizado from '@/components/AvisoDesactualizado.vue';
import EstadoError from '@/components/EstadoError.vue';
import EstadoVacio from '@/components/EstadoVacio.vue';
import { logout } from '@/services/authApi';
import { guardarCacheProductos, hayConexion, leerCacheProductos } from '@/services/cacheProductos';
import { normalizarError, type ErrorNormalizado } from '@/services/errores';
import { formatearPrecio, listadoDesactualizado, listarProductos, type Producto } from '@/services/productosApi';
import { usuarioActual } from '@/services/sesion';

const router = useRouter();

const productos = ref<Producto[]>([]);
const pagina = ref(0);
const ultimaPagina = ref(1);
const cargandoInicial = ref(true);
const error = ref<ErrorNormalizado | null>(null);
/** Si tiene valor, lo que se ve viene de la caché (sin conexión). */
const actualizadoEnCache = ref<string | null>(null);

const hayMasPaginas = computed(
  () => !cargandoInicial.value && !actualizadoEnCache.value && !error.value && pagina.value < ultimaPagina.value,
);

let escuchaRed: PluginListenerHandle | null = null;

async function mostrarCache(motivo: ErrorNormalizado): Promise<void> {
  const cache = await leerCacheProductos();
  if (cache) {
    productos.value = cache.productos;
    pagina.value = cache.pagina;
    ultimaPagina.value = cache.ultimaPagina;
    actualizadoEnCache.value = cache.actualizadoEn;
    error.value = null;
  } else {
    error.value = motivo.sinConexion
      ? { ...motivo, mensaje: 'Sin conexión a internet y todavía no hay datos guardados en el teléfono.' }
      : motivo;
  }
}

/** Trae la primera página y reemplaza el listado. */
async function recargarPrimeraPagina(): Promise<void> {
  if (!(await hayConexion())) {
    await mostrarCache({
      mensaje: 'Sin conexión a internet.',
      status: null,
      codigo: null,
      errores: {},
      sinConexion: true,
    });
    return;
  }

  try {
    const respuesta = await listarProductos(1);
    productos.value = respuesta.data;
    pagina.value = respuesta.meta.current_page;
    ultimaPagina.value = respuesta.meta.last_page;
    actualizadoEnCache.value = null;
    error.value = null;
    await guardarCacheProductos({
      productos: productos.value,
      pagina: pagina.value,
      ultimaPagina: ultimaPagina.value,
    });
  } catch (e) {
    const normalizado = normalizarError(e);
    if (normalizado.sinConexion) {
      await mostrarCache(normalizado);
    } else {
      error.value = normalizado;
    }
  }
}

async function cargarInicial(): Promise<void> {
  cargandoInicial.value = true;
  error.value = null;
  await recargarPrimeraPagina();
  cargandoInicial.value = false;
}

async function alArrastrar(evento: RefresherCustomEvent): Promise<void> {
  await recargarPrimeraPagina();
  await evento.target.complete();
  if (actualizadoEnCache.value) {
    await avisar('Seguís sin conexión: se muestran los datos guardados.', 'warning');
  }
}

async function cargarSiguiente(evento: InfiniteScrollCustomEvent): Promise<void> {
  try {
    const respuesta = await listarProductos(pagina.value + 1);
    productos.value = [...productos.value, ...respuesta.data];
    pagina.value = respuesta.meta.current_page;
    ultimaPagina.value = respuesta.meta.last_page;
    await guardarCacheProductos({
      productos: productos.value,
      pagina: pagina.value,
      ultimaPagina: ultimaPagina.value,
    });
  } catch (e) {
    await avisar(normalizarError(e).mensaje, 'danger');
  } finally {
    await evento.target.complete();
  }
}

async function avisar(mensaje: string, color: 'warning' | 'danger' | 'success'): Promise<void> {
  const toast = await toastController.create({ message: mensaje, color, duration: 2500, position: 'bottom' });
  await toast.present();
}

function abrirDetalle(id: number): void {
  router.push({ name: 'producto-detalle', params: { id } });
}

async function confirmarLogout(): Promise<void> {
  const alerta = await alertController.create({
    header: 'Cerrar sesión',
    message: '¿Querés salir de tu cuenta?',
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      {
        text: 'Salir',
        role: 'confirm',
        handler: async () => {
          await logout();
          await router.replace({ name: 'login' });
        },
      },
    ],
  });
  await alerta.present();
}

onMounted(async () => {
  await cargarInicial();

  // Cuando vuelve la conexión y se está viendo la caché, se actualiza solo.
  escuchaRed = await Network.addListener('networkStatusChange', async (estado) => {
    if (estado.connected && actualizadoEnCache.value) {
      await recargarPrimeraPagina();
      if (!actualizadoEnCache.value) {
        await avisar('Conexión restablecida: menú actualizado.', 'success');
      }
    }
  });
});

onIonViewWillEnter(async () => {
  if (listadoDesactualizado.value) {
    listadoDesactualizado.value = false;
    await recargarPrimeraPagina();
  }
});

onUnmounted(() => {
  escuchaRed?.remove();
});
</script>

<style scoped>
.contenedor-ancho {
  max-width: 1200px;
  margin: 0 auto;
  /* Deja libre el espacio del FAB al final de la lista. */
  padding-bottom: 72px;
}

.saludo {
  margin: 0 0 12px;
}

.tarjeta {
  margin: 6px;
}

.sin-imagen {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: var(--ion-color-light);
  border-radius: 4px;
}

.sin-imagen ion-icon {
  font-size: 28px;
}

ion-thumbnail img {
  object-fit: cover;
  border-radius: 4px;
}
</style>
