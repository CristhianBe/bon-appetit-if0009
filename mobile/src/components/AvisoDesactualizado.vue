<template>
  <ion-item color="warning" lines="none" class="aviso" role="status">
    <ion-icon slot="start" :icon="cloudOfflineOutline" aria-hidden="true" />
    <ion-label class="ion-text-wrap">
      <strong>Datos no actualizados</strong> — última actualización: {{ fechaFormateada }}
    </ion-label>
  </ion-item>
</template>

<script setup lang="ts">
import { IonIcon, IonItem, IonLabel } from '@ionic/vue';
import { cloudOfflineOutline } from 'ionicons/icons';
import { computed } from 'vue';
import { formatearFechaHora } from '@/services/cacheProductos';

const props = defineProps<{ actualizadoEn: string }>();

const fechaFormateada = computed(() => formatearFechaHora(props.actualizadoEn));
</script>

<style scoped>
.aviso {
  border-radius: 8px;
  margin-bottom: 12px;
}
</style>
