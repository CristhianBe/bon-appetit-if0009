<template>
  <ion-app>
    <ion-router-outlet />
  </ion-app>
</template>

<script setup lang="ts">
import { App as CapacitorApp } from '@capacitor/app';
import type { PluginListenerHandle } from '@capacitor/core';
import { IonApp, IonRouterOutlet } from '@ionic/vue';
import { onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

/** Pantallas "raíz": atrás desde acá cierra la app en vez de navegar. */
const RUTAS_RAIZ = ['login', 'productos'];

let escuchaBotonAtras: PluginListenerHandle | null = null;

onMounted(async () => {
  // Botón atrás físico de Android. Al iniciar sesión se usa router.replace, así que el
  // login nunca queda en el historial; además, desde el listado (raíz de la zona
  // autenticada) atrás sale de la app en lugar de intentar volver al login.
  escuchaBotonAtras = await CapacitorApp.addListener('backButton', ({ canGoBack }) => {
    const rutaActual = String(router.currentRoute.value.name ?? '');

    if (RUTAS_RAIZ.includes(rutaActual)) {
      CapacitorApp.exitApp();
      return;
    }

    if (canGoBack) {
      router.back();
    } else {
      router.replace({ name: 'productos' });
    }
  });
});

onUnmounted(() => {
  escuchaBotonAtras?.remove();
});
</script>
