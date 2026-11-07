<template>
  <div class="estado-centrado" role="alert">
    <ion-icon :icon="sinConexion ? cloudOfflineOutline : alertCircleOutline" color="danger" aria-hidden="true" />
    <ion-text color="danger">
      <h2>{{ titulo }}</h2>
    </ion-text>
    <ion-text color="medium">
      <p>{{ mensaje }}</p>
    </ion-text>
    <ion-button @click="emit('reintentar')">
      <ion-icon slot="start" :icon="refreshOutline" aria-hidden="true" />
      Reintentar
    </ion-button>
  </div>
</template>

<script setup lang="ts">
import { IonButton, IonIcon, IonText } from '@ionic/vue';
import { alertCircleOutline, cloudOfflineOutline, refreshOutline } from 'ionicons/icons';

withDefaults(
  defineProps<{
    mensaje: string;
    titulo?: string;
    sinConexion?: boolean;
  }>(),
  { titulo: 'No se pudieron cargar los datos', sinConexion: false },
);

const emit = defineEmits<{ reintentar: [] }>();
</script>
