<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-buttons slot="start">
          <ion-back-button default-href="/productos" text="" />
        </ion-buttons>
        <ion-title>Detalle</ion-title>
        <ion-buttons v-if="producto && !actualizadoEnCache" slot="end">
          <ion-button aria-label="Editar producto" @click="editar">
            <ion-icon slot="icon-only" :icon="createOutline" />
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content class="contenido-seguro">
      <div class="contenedor">
        <!-- Carga -->
        <ion-card v-if="cargando && !producto" aria-busy="true" aria-label="Cargando producto">
          <ion-skeleton-text :animated="true" class="skeleton-imagen" />
          <ion-card-header>
            <ion-skeleton-text :animated="true" style="width: 70%; height: 24px" />
            <ion-skeleton-text :animated="true" style="width: 30%" />
          </ion-card-header>
          <ion-card-content>
            <ion-skeleton-text :animated="true" />
            <ion-skeleton-text :animated="true" style="width: 85%" />
          </ion-card-content>
        </ion-card>

        <!-- 404: el producto no existe -->
        <EstadoVacio
          v-else-if="noEncontrado"
          titulo="Producto no encontrado"
          mensaje="Puede que haya sido eliminado del menú."
          :icono="searchOutline"
        >
          <ion-button @click="router.replace({ name: 'productos' })">Volver al menú</ion-button>
        </EstadoVacio>

        <!-- Error -->
        <EstadoError
          v-else-if="error"
          :mensaje="error.mensaje"
          :sin-conexion="error.sinConexion"
          @reintentar="cargar"
        />

        <!-- Detalle -->
        <template v-else-if="producto">
          <AvisoDesactualizado v-if="actualizadoEnCache" :actualizado-en="actualizadoEnCache" />

          <ion-card>
            <img v-if="producto.imagen_url" :src="producto.imagen_url" :alt="producto.nombre" class="imagen" />
            <ion-card-header>
              <ion-card-subtitle>{{ producto.categoria?.nombre ?? 'Sin categoría' }}</ion-card-subtitle>
              <ion-card-title>{{ producto.nombre }}</ion-card-title>
            </ion-card-header>
            <ion-card-content>
              <ion-list lines="inset">
                <ion-item>
                  <ion-icon slot="start" :icon="pricetagOutline" color="primary" aria-hidden="true" />
                  <ion-label>Precio</ion-label>
                  <ion-text slot="end" color="primary">
                    <strong>{{ formatearPrecio(producto.precio) }}</strong>
                  </ion-text>
                </ion-item>
                <ion-item>
                  <ion-icon slot="start" :icon="checkmarkCircleOutline" color="primary" aria-hidden="true" />
                  <ion-label>Estado</ion-label>
                  <ion-badge slot="end" :color="producto.disponible ? 'success' : 'medium'">
                    {{ producto.disponible ? 'Disponible' : 'Agotado' }}
                  </ion-badge>
                </ion-item>
                <ion-item lines="none">
                  <ion-label class="ion-text-wrap">
                    <h3>Descripción</h3>
                    <p>{{ producto.descripcion || 'Sin descripción.' }}</p>
                  </ion-label>
                </ion-item>
              </ion-list>
            </ion-card-content>
          </ion-card>

          <ion-button v-if="!actualizadoEnCache" expand="block" @click="editar">
            <ion-icon slot="start" :icon="createOutline" />
            Editar producto
          </ion-button>
        </template>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import {
  IonBackButton,
  IonBadge,
  IonButton,
  IonButtons,
  IonCard,
  IonCardContent,
  IonCardHeader,
  IonCardSubtitle,
  IonCardTitle,
  IonContent,
  IonHeader,
  IonIcon,
  IonItem,
  IonLabel,
  IonList,
  IonPage,
  IonSkeletonText,
  IonText,
  IonTitle,
  IonToolbar,
  onIonViewWillEnter,
} from '@ionic/vue';
import { checkmarkCircleOutline, createOutline, pricetagOutline, searchOutline } from 'ionicons/icons';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AvisoDesactualizado from '@/components/AvisoDesactualizado.vue';
import EstadoError from '@/components/EstadoError.vue';
import EstadoVacio from '@/components/EstadoVacio.vue';
import { leerCacheProductos } from '@/services/cacheProductos';
import { normalizarError, type ErrorNormalizado } from '@/services/errores';
import { formatearPrecio, obtenerProducto, type Producto } from '@/services/productosApi';

const props = defineProps<{ id: number }>();

const router = useRouter();

const producto = ref<Producto | null>(null);
const cargando = ref(true);
const error = ref<ErrorNormalizado | null>(null);
const noEncontrado = ref(false);
const actualizadoEnCache = ref<string | null>(null);

async function cargar(): Promise<void> {
  cargando.value = true;
  error.value = null;
  noEncontrado.value = false;

  try {
    producto.value = await obtenerProducto(props.id);
    actualizadoEnCache.value = null;
  } catch (e) {
    const normalizado = normalizarError(e);

    if (normalizado.codigo === 'recurso_no_encontrado') {
      producto.value = null;
      noEncontrado.value = true;
    } else if (normalizado.sinConexion) {
      // Sin red: si el producto está en el último listado guardado, se muestra desde ahí.
      const cache = await leerCacheProductos();
      const enCache = cache?.productos.find((p) => p.id === props.id);
      if (cache && enCache) {
        producto.value = enCache;
        actualizadoEnCache.value = cache.actualizadoEn;
      } else {
        producto.value = null;
        error.value = normalizado;
      }
    } else {
      producto.value = null;
      error.value = normalizado;
    }
  } finally {
    cargando.value = false;
  }
}

function editar(): void {
  router.push({ name: 'producto-editar', params: { id: props.id } });
}

// Se vuelve a consultar cada vez que la vista entra en pantalla (por ejemplo, al volver
// de editar), sin mostrar el skeleton si ya hay datos.
onIonViewWillEnter(cargar);
</script>

<style scoped>
.imagen {
  width: 100%;
  max-height: 280px;
  object-fit: cover;
}

.skeleton-imagen {
  height: 200px;
  margin: 0;
}
</style>
