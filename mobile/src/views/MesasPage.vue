<template>
  <ion-page>
    <AppHeader titulo="Control de mesas" />

    <ion-content class="contenido-seguro">
      <ion-refresher slot="fixed" @ion-refresh="refrescar">
        <ion-refresher-content />
      </ion-refresher>

      <div class="contenedor-ancho">
        <ion-text v-if="usuarioActual" color="medium">
          <p class="saludo">Hola, {{ usuarioActual.name }} · {{ rolActual ? ETIQUETA_ROL[rolActual] : 'sin rol' }}</p>
        </ion-text>

        <div v-if="cargando && !mesas.length" class="mesas" aria-busy="true">
          <ion-skeleton-text v-for="n in 6" :key="n" animated class="esqueleto" />
        </div>

        <EstadoError
          v-else-if="error && !mesas.length"
          :mensaje="error.mensaje"
          :sin-conexion="error.sinConexion"
          @reintentar="cargar"
        />

        <EstadoVacio
          v-else-if="!mesas.length"
          titulo="Todavía no hay mesas"
          mensaje="El administrador puede registrarlas desde el panel web."
        />

        <template v-else>
          <section class="resumen" aria-label="Resumen del salón">
            <div class="dato"><span>Libres</span><strong class="ok">{{ cuenta('libre') }}</strong></div>
            <div class="dato"><span>Ocupadas</span><strong class="aviso">{{ cuenta('ocupada') + cuenta('retraso') }}</strong></div>
            <div class="dato"><span>Reservadas</span><strong>{{ cuenta('reservada') }}</strong></div>
            <div class="dato dato--ancho"><span>Facturación activa</span><strong class="dinero">{{ colones(facturacion) }}</strong></div>
          </section>

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

          <div class="mesas">
            <article v-for="m in visibles" :key="m.id" :class="['mesa', `mesa--${visual(m)}`]">
              <header>
                <h2><span class="sr-only">Mesa </span>{{ m.numero }}</h2>
                <ion-badge :color="BADGE[visual(m)].color">{{ BADGE[visual(m)].texto }}</ion-badge>
              </header>
              <p class="meta"><ion-icon :icon="peopleOutline" aria-hidden="true" /> {{ m.capacidad }} pax · {{ m.zona }}</p>

              <template v-if="m.comanda_activa">
                <p class="tiempo"><ion-icon :icon="timeOutline" aria-hidden="true" /> {{ duracion(minutosDesde(m.comanda_activa.abierta_en)) }}</p>
                <p class="monto">{{ colones(m.comanda_activa.total) }}</p>
                <p class="meta">Mesero: {{ m.comanda_activa.mesero ?? '—' }}</p>
                <ion-button v-if="puedeVer(m)" size="small" expand="block" @click="verComanda(m.comanda_activa.id)">Ver comanda</ion-button>
                <ion-button v-else size="small" expand="block" fill="outline" disabled>Otro mesero</ion-button>
              </template>

              <template v-else-if="reservaDe(m)">
                <p class="nombre">{{ reservaDe(m)!.nombre }}</p>
                <p class="meta">Hora: {{ reservaDe(m)!.hora }} · {{ reservaDe(m)!.personas }} pax</p>
                <ion-button v-if="puedeAbrir" size="small" expand="block" fill="outline" @click="abrir(m, reservaDe(m))">Recibir</ion-button>
              </template>

              <template v-else>
                <p class="libre">Disponible</p>
                <ion-button v-if="puedeAbrir" size="small" expand="block" @click="abrir(m)">Abrir comanda</ion-button>
              </template>
            </article>
          </div>
        </template>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import {
  alertController, IonBadge, IonButton, IonContent, IonIcon, IonPage, IonRefresher, IonRefresherContent,
  IonSkeletonText, IonText, onIonViewDidLeave, onIonViewWillEnter, toastController,
} from '@ionic/vue';
import { peopleOutline, timeOutline } from 'ionicons/icons';
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import AppHeader from '@/components/AppHeader.vue';
import EstadoError from '@/components/EstadoError.vue';
import EstadoVacio from '@/components/EstadoVacio.vue';
import { normalizarError, type ErrorNormalizado } from '@/services/errores';
import { colones, duracion, fechaIso, minutosDesde, MINUTOS_RETRASO } from '@/services/formato';
import {
  abrirComanda, listarMesas, listarReservasDelDia, marcarReservaCumplida, type Mesa, type Reserva,
} from '@/services/salonApi';
import { ETIQUETA_ROL, rolActual, tieneRol, usuarioActual } from '@/services/sesion';

type Visual = 'libre' | 'ocupada' | 'retraso' | 'reservada';

const BADGE: Record<Visual, { texto: string; color: string }> = {
  libre: { texto: 'Libre', color: 'success' },
  ocupada: { texto: 'Ocupada', color: 'warning' },
  retraso: { texto: 'Retraso', color: 'danger' },
  reservada: { texto: 'Reservada', color: 'medium' },
};

const router = useRouter();
const mesas = ref<Mesa[]>([]);
const reservas = ref<Reserva[]>([]);
const cargando = ref(false);
const error = ref<ErrorNormalizado | null>(null);
const zona = ref('');

const puedeAbrir = computed(() => tieneRol('administrador', 'mesero'));
const zonas = computed(() => ['', ...new Set(mesas.value.map((m) => m.zona).filter(Boolean))]);
const visibles = computed(() => mesas.value.filter((m) => !zona.value || m.zona === zona.value));
const facturacion = computed(() => mesas.value.reduce((s, m) => s + (m.comanda_activa?.total ?? 0), 0));

function reservaDe(m: Mesa): Reserva | null {
  return reservas.value.filter((r) => r.mesa_id === m.id).sort((a, b) => a.hora.localeCompare(b.hora))[0] ?? null;
}

function visual(m: Mesa): Visual {
  if (m.comanda_activa || m.estado?.codigo === 'ocupada') {
    return minutosDesde(m.comanda_activa?.abierta_en) > MINUTOS_RETRASO ? 'retraso' : 'ocupada';
  }
  return m.estado?.codigo === 'reservada' || reservaDe(m) ? 'reservada' : 'libre';
}

const cuenta = (v: Visual): number => mesas.value.filter((m) => visual(m) === v).length;

function puedeVer(m: Mesa): boolean {
  return tieneRol('administrador', 'cajero') || m.comanda_activa?.mesero_id === usuarioActual.value?.id;
}

async function cargar(): Promise<void> {
  cargando.value = true;
  try {
    const [listaMesas, listaReservas] = await Promise.all([listarMesas(), listarReservasDelDia(fechaIso())]);
    mesas.value = listaMesas;
    reservas.value = listaReservas;
    error.value = null;
  } catch (e) {
    error.value = normalizarError(e);
    if (mesas.value.length) await avisar(error.value.mensaje);
  } finally {
    cargando.value = false;
  }
}

async function refrescar(evento: CustomEvent): Promise<void> {
  await cargar();
  (evento.target as HTMLIonRefresherElement).complete();
}

function verComanda(id: number): void {
  router.push({ name: 'comandas', query: { abrir: String(id) } });
}

async function abrir(m: Mesa, reserva: Reserva | null = null): Promise<void> {
  const alerta = await alertController.create({
    header: reserva ? `Recibir a ${reserva.nombre}` : `Abrir la mesa ${m.numero}`,
    subHeader: `${m.zona} · capacidad ${m.capacidad}`,
    inputs: [
      { name: 'personas', type: 'number', min: 1, max: 50, value: reserva?.personas ?? Math.min(2, m.capacidad), placeholder: 'Personas', attributes: { 'aria-label': 'Personas en la mesa' } },
      { name: 'notas', type: 'text', value: reserva?.notas ?? '', placeholder: 'Notas (alergias, celebración…)', attributes: { maxlength: 255, 'aria-label': 'Notas' } },
    ],
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      { text: 'Abrir comanda', role: 'confirm' },
    ],
  });
  await alerta.present();
  const { role, data } = await alerta.onDidDismiss();
  if (role !== 'confirm') return;

  const personas = Number(data?.values?.personas);
  if (!personas || personas < 1) {
    await avisar('Indicá cuántas personas hay en la mesa.');
    return;
  }

  try {
    const comanda = await abrirComanda(m.id, usuarioActual.value!.id, personas, String(data.values.notas ?? '').trim() || null);
    if (reserva) await marcarReservaCumplida(reserva.id).catch(() => undefined);
    router.push({ name: 'comandas', query: { abrir: String(comanda.id) } });
  } catch (e) {
    const err = normalizarError(e);
    await avisar(err.status === 403 ? 'Tu rol no puede abrir comandas.' : err.mensaje);
    if (err.codigo === 'mesa_no_disponible') await cargar();
  }
}

async function avisar(mensaje: string): Promise<void> {
  const toast = await toastController.create({ message: mensaje, duration: 2600, position: 'bottom', positionAnchor: 'barra-inferior' });
  await toast.present();
}

let temporizador: ReturnType<typeof setInterval> | undefined;
onIonViewWillEnter(() => {
  cargar();
  temporizador = setInterval(() => { if (!document.hidden) cargar(); }, 30000);
});
onIonViewDidLeave(() => clearInterval(temporizador));
</script>

<style scoped>
.contenedor-ancho { max-width: 900px; margin: 0 auto; }
.saludo { margin: 0 0 12px; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }

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
.chip { flex-shrink: 0; min-height: 36px; padding: 7px 14px; border-radius: 8px; border: 1px solid var(--ba-borde); background: #fff; font: inherit; font-size: 13px; font-weight: 500; color: var(--ion-text-color); }
.chip[aria-pressed='true'] { background: var(--ion-color-primary); border-color: var(--ion-color-primary); color: #fff; }

.mesas { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
@media (min-width: 640px) { .mesas { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
.esqueleto { height: 150px; border-radius: 12px; margin: 0; }

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
.mesa ion-button { margin: 6px 0 0; margin-top: auto; }
</style>
