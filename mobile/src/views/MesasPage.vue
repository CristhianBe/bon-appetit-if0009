<template>
  <ion-page>
    <AppHeader titulo="Control de mesas" />

    <ion-content class="contenido-seguro">
      <div class="contenedor-ancho">
        <ion-text v-if="usuarioActual" color="medium">
          <p class="saludo">Hola, {{ usuarioActual.name }} · turno tarde</p>
        </ion-text>

        <!-- Resumen del turno -->
        <section class="resumen" aria-label="Resumen del salón">
          <div class="dato"><span>Libres</span><strong class="ok">{{ cuenta('libre') }}</strong></div>
          <div class="dato"><span>Ocupadas</span><strong class="aviso">{{ cuenta('ocupada') }}</strong></div>
          <div class="dato"><span>Reservadas</span><strong>{{ cuenta('reservada') }}</strong></div>
          <div class="dato dato--ancho"><span>Facturación activa</span><strong class="dinero">{{ colones(facturacion) }}</strong></div>
        </section>

        <!-- Filtro por zona -->
        <div class="chips" role="group" aria-label="Filtrar por zona">
          <button
            v-for="z in zonas"
            :key="z"
            type="button"
            class="chip"
            :aria-pressed="zona === z"
            @click="zona = z"
          >{{ z || 'Todas' }}</button>
        </div>

        <!-- Mesas -->
        <div class="mesas">
          <article
            v-for="m in visibles"
            :key="m.numero"
            :class="['mesa', `mesa--${m.estado}`, { 'mesa--retraso': m.comanda?.retraso }]"
          >
            <header>
              <h2>{{ m.numero }}</h2>
              <ion-badge :color="color(m)">{{ etiqueta(m) }}</ion-badge>
            </header>
            <p class="meta"><ion-icon :icon="peopleOutline" aria-hidden="true" /> {{ m.capacidad }} pax · {{ m.zona }}</p>

            <template v-if="m.comanda">
              <p class="tiempo"><ion-icon :icon="timeOutline" aria-hidden="true" /> {{ m.comanda.minutos }} min</p>
              <p class="monto">{{ colones(m.comanda.total) }}</p>
              <p class="meta">Mesero: {{ m.comanda.mesero }}</p>
              <ion-button size="small" expand="block" @click="verComanda(m.comanda.codigo)">Ver</ion-button>
            </template>
            <template v-else-if="m.reserva">
              <p class="nombre">{{ m.reserva.nombre }}</p>
              <p class="meta">Hora: {{ m.reserva.hora }}</p>
              <ion-button size="small" expand="block" fill="outline" @click="avisar(`Mesa ${m.numero} habilitada (demostración)`)">Habilitar</ion-button>
            </template>
            <template v-else>
              <p class="libre">Disponible</p>
              <ion-button size="small" expand="block" @click="avisar(`Comanda abierta en la mesa ${m.numero} (demostración)`)">Abrir</ion-button>
            </template>
          </article>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { IonBadge, IonButton, IonContent, IonIcon, IonPage, IonText, toastController } from '@ionic/vue';
import { peopleOutline, timeOutline } from 'ionicons/icons';
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppHeader from '@/components/AppHeader.vue';
import { colones, mesas as cargarMesas, type Mesa } from '@/services/datosDemo';
import { usuarioActual } from '@/services/sesion';

const router = useRouter();
const todas = cargarMesas();
const zonas = ['', 'Interior', 'Terraza', 'Barra'];
const zona = ref('');

const visibles = computed(() => todas.filter((m) => !zona.value || m.zona === zona.value));
const cuenta = (estado: Mesa['estado']): number => todas.filter((m) => m.estado === estado).length;
const facturacion = computed(() => todas.reduce((s, m) => s + (m.comanda?.total ?? 0), 0));

function etiqueta(m: Mesa): string {
  if (m.comanda?.retraso) return 'Retraso';
  return { libre: 'Libre', ocupada: 'Ocupada', reservada: 'Reservada' }[m.estado];
}
function color(m: Mesa): string {
  if (m.comanda?.retraso) return 'danger';
  return { libre: 'success', ocupada: 'warning', reservada: 'medium' }[m.estado];
}

function verComanda(codigo: string): void {
  router.push({ name: 'comandas', query: { abrir: codigo } });
}

async function avisar(mensaje: string): Promise<void> {
  const toast = await toastController.create({ message: mensaje, duration: 2200, position: 'bottom', positionAnchor: 'barra-inferior' });
  await toast.present();
}
</script>

<style scoped>
.contenedor-ancho { max-width: 900px; margin: 0 auto; }
.saludo { margin: 0 0 12px; }

.resumen { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 14px; }
.dato { background: #fff; border: 1px solid var(--ba-borde); border-radius: 12px; padding: 10px 12px; display: flex; flex-direction: column; gap: 2px; }
.dato span { font-size: 12px; color: var(--ion-color-medium); }
.dato strong { font-size: 22px; line-height: 1.1; }
.dato--ancho { grid-column: 1 / -1; background: var(--ion-color-primary); border-color: var(--ion-color-primary); }
.dato--ancho span { color: rgb(255 255 255 / 0.75); }
.dato--ancho strong.dinero { font-family: var(--ba-titulo); color: #fff; font-size: 26px; }
.ok { color: var(--ion-color-success); }
.aviso { color: #b45309; }

.chips { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 12px; }
.chip { flex-shrink: 0; padding: 7px 14px; border-radius: 8px; border: 1px solid var(--ba-borde); background: #fff; font: inherit; font-size: 13px; font-weight: 500; color: var(--ion-text-color); }
.chip[aria-pressed='true'] { background: var(--ion-color-primary); border-color: var(--ion-color-primary); color: #fff; }

.mesas { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
@media (min-width: 640px) { .mesas { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

.mesa { display: flex; flex-direction: column; gap: 4px; background: #fff; border: 1.5px solid var(--ba-borde); border-radius: 12px; padding: 12px; }
.mesa--libre { border-color: #2f6b3f; }
.mesa--ocupada { border-color: #d97706; }
.mesa--retraso { border-color: #b42318; }
.mesa header { display: flex; justify-content: space-between; align-items: flex-start; }
.mesa h2 { font-family: var(--ba-titulo); font-size: 26px; font-weight: 700; margin: 0; line-height: 1; color: var(--ion-text-color); }
.meta { margin: 0; display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--ion-color-medium); }
.tiempo { margin: 0; display: flex; align-items: center; gap: 4px; font-size: 12.5px; font-weight: 600; color: #b45309; }
.mesa--retraso .tiempo { color: #b42318; }
.monto { margin: 0; font-size: 19px; font-weight: 700; }
.nombre { margin: 0; font-weight: 700; }
.libre { margin: 0; font-weight: 600; color: var(--ion-color-success); }
.mesa ion-button { margin: 6px 0 0; }
</style>
