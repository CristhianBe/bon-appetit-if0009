<template>
  <ion-header>
    <ion-toolbar color="primary">
      <ion-buttons slot="start">
        <ion-menu-button></ion-menu-button>
      </ion-buttons>
      <!-- El título dinámico -->
      <ion-title>{{ titulo }}</ion-title>
       <ion-buttons slot="end">
          <ion-button aria-label="Cerrar sesión" @click="confirmarLogout">
            <ion-icon slot="icon-only" :icon="logOutOutline" />
          </ion-button>
        </ion-buttons>
    </ion-toolbar>
  </ion-header>
</template>

<script setup lang="ts">
import { IonHeader, IonToolbar, IonTitle, IonButtons, IonMenuButton } from '@ionic/vue';
import { IonButton, IonIcon } from '@ionic/vue';
import { logOutOutline } from 'ionicons/icons';
import { logout } from '@/services/authApi';
import { alertController } from '@ionic/vue';
import { useRouter } from 'vue-router';

const router = useRouter();

defineProps({
  titulo: {
    type: String,
    default: 'Bon Appétit'
  }
});

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
</script>