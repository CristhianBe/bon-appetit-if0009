<template>
  <ion-app>

    <!-- Menú lateral (se abre con el botón ☰ del encabezado) -->
    <ion-menu content-id="main-content" type="overlay">
      <ion-header>
        <ion-toolbar color="primary">
          <ion-title class="titulo-marca">Bon Appétit</ion-title>
        </ion-toolbar>
      </ion-header>
      <ion-content>
        <ion-list lines="none" class="menu-lista">
          <ion-menu-toggle v-for="s in secciones" :key="s.ruta" :auto-hide="false">
            <ion-item button :detail="false" :class="{ activo: rutaActual === s.ruta }" @click="ir(s.ruta)">
              <ion-icon slot="start" :icon="s.icono" aria-hidden="true" />
              <ion-label>{{ s.texto }}</ion-label>
            </ion-item>
          </ion-menu-toggle>
        </ion-list>
      </ion-content>
    </ion-menu>

    <ion-router-outlet id="main-content"></ion-router-outlet>

    <!-- Barra inferior: solo en las pantallas principales -->
    <nav v-if="mostrarBarra" id="barra-inferior" class="barra-inferior" aria-label="Secciones">
      <button
        v-for="s in secciones"
        :key="s.ruta"
        type="button"
        :class="{ activo: rutaActual === s.ruta }"
        :aria-current="rutaActual === s.ruta ? 'page' : undefined"
        @click="ir(s.ruta)"
      >
        <ion-icon :icon="s.icono" aria-hidden="true" />
        <span>{{ s.corto }}</span>
      </button>
    </nav>
  </ion-app>
</template>

<script setup lang="ts">
import { App as CapacitorApp } from '@capacitor/app';
import type { PluginListenerHandle } from '@capacitor/core';
import {
  IonApp, IonRouterOutlet, IonTitle, IonToolbar, IonHeader,
  IonContent, IonMenu, IonMenuToggle, IonList, IonItem, IonLabel, IonIcon,
} from '@ionic/vue';
import { gridOutline, receiptOutline, restaurantOutline } from 'ionicons/icons';
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const router = useRouter();
const route = useRoute();

const secciones = [
  { ruta: 'mesas', texto: 'Mesas', corto: 'Mesas', icono: gridOutline },
  { ruta: 'comandas', texto: 'Comandas', corto: 'Comandas', icono: receiptOutline },
  { ruta: 'productos', texto: 'Menú y catálogo', corto: 'Menú', icono: restaurantOutline },
];

/** Pantallas "raíz": atrás desde acá cierra la app en vez de navegar. */
const RUTAS_RAIZ = ['login', ...secciones.map((s) => s.ruta)];

const rutaActual = computed(() => String(route.name ?? ''));
const mostrarBarra = computed(() => secciones.some((s) => s.ruta === rutaActual.value));

// Con la barra visible, el contenido y el botón flotante dejan espacio abajo (ver variables.css).
watch(mostrarBarra, (visible) => document.body.classList.toggle('con-barra', visible), { immediate: true });

function ir(ruta: string): void {
  if (ruta !== rutaActual.value) router.replace({ name: ruta });
}

let escuchaBotonAtras: PluginListenerHandle | null = null;

onMounted(async () => {
  // Botón atrás físico de Android. Al iniciar sesión se usa router.replace, así que el
  // login nunca queda en el historial; además, desde una pantalla raíz atrás sale de la
  // app en lugar de intentar volver al login.
  escuchaBotonAtras = await CapacitorApp.addListener('backButton', ({ canGoBack }) => {
    if (RUTAS_RAIZ.includes(rutaActual.value)) {
      CapacitorApp.exitApp();
      return;
    }

    if (canGoBack) {
      router.back();
    } else {
      router.replace({ name: 'mesas' });
    }
  });
});

onUnmounted(() => {
  escuchaBotonAtras?.remove();
});
</script>

<style scoped>
.menu-lista { padding-top: 8px; }
.menu-lista ion-item { --border-radius: 8px; margin: 2px 8px; font-weight: 500; }
.menu-lista ion-item.activo { --background: var(--ba-vino-suave); --color: var(--ion-color-primary); }
.menu-lista ion-item.activo ion-icon { color: var(--ion-color-primary); }
</style>
