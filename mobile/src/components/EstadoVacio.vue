<template>
  <div class="estado-centrado">
    <ion-icon :icon="icono" color="medium" aria-hidden="true" />
    <ion-text>
      <h2>{{ titulo }}</h2>
    </ion-text>
    <ion-text color="medium">
      <p>{{ mensaje }}</p>
    </ion-text>
    <slot />
  </div>
</template>

<script setup lang="ts">
import { IonIcon, IonText } from '@ionic/vue';
import { fileTrayOutline } from 'ionicons/icons';

withDefaults(
  defineProps<{
    titulo: string;
    mensaje: string;
    icono?: string;
  }>(),
  { icono: fileTrayOutline },
);
</script>
