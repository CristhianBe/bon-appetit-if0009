<template>
  <ion-page>
    <AppHeader titulo="Comandas activas" />

    <ion-content class="contenido-seguro">
      <div class="contenedor-ancho">
        <!-- Resumen -->
        <section class="resumen" aria-label="Resumen de comandas">
          <div class="dato"><span>Activas</span><strong>{{ todas.length }}</strong></div>
          <div class="dato"><span>En preparación</span><strong class="aviso">{{ cuenta('en_preparacion') }}</strong></div>
          <div class="dato dato--ancho"><span>Facturación en curso</span><strong class="dinero">{{ colones(total) }}</strong></div>
        </section>

        <div v-if="atrasadas.length" class="alerta" role="alert">
          <ion-icon :icon="warningOutline" aria-hidden="true" />
          <span>
            {{ atrasadas.length }} {{ atrasadas.length === 1 ? 'comanda supera' : 'comandas superan' }} los {{ MINUTOS_RETRASO }} min:
            {{ atrasadas.map((c) => `Mesa ${c.mesa} (${c.minutos} min)`).join(', ') }}.
          </span>
        </div>

        <!-- Filtro por estado -->
        <div class="chips" role="group" aria-label="Filtrar por estado">
          <button v-for="f in filtros" :key="f.valor" type="button" class="chip" :aria-pressed="filtro === f.valor" @click="filtro = f.valor">
            {{ f.texto }} ({{ f.valor ? cuenta(f.valor) : todas.length }})
          </button>
        </div>

        <!-- Lista -->
        <article
          v-for="c in visibles"
          :key="c.codigo"
          :class="['comanda', { 'comanda--retraso': c.retraso, 'comanda--abierta': abierta === c.codigo }]"
        >
          <button type="button" class="cabecera" :aria-expanded="abierta === c.codigo" @click="alternar(c.codigo)">
            <span class="fila">
              <span class="mesa-titulo">Mesa {{ c.mesa }}</span>
              <ion-badge :color="c.retraso ? 'danger' : ETIQUETA_ESTADO[c.estado].color">
                {{ c.retraso ? 'Retraso' : ETIQUETA_ESTADO[c.estado].texto }}
              </ion-badge>
            </span>
            <span class="sub">#{{ c.codigo }} · {{ c.mesero }} · {{ c.cantidadItems }} productos</span>
            <span class="fila">
              <span class="tiempo"><ion-icon :icon="timeOutline" aria-hidden="true" /> {{ c.minutos }} min</span>
              <span class="total">{{ colones(c.total) }}</span>
            </span>
          </button>

          <div v-if="abierta === c.codigo" class="detalle">
            <ul class="items">
              <li v-for="(i, n) in c.items" :key="n">
                <span class="cant">{{ i.cant }}×</span>
                <span class="nom">
                  {{ i.nombre }}
                  <small v-if="i.nota" class="nota">{{ i.nota }}</small>
                </span>
                <span class="pre">{{ colones(i.cant * i.precio) }}</span>
              </li>
            </ul>

            <div v-if="c.alergia" class="alergia">
              <ion-icon :icon="warningOutline" aria-hidden="true" /> <strong>Alergia:</strong> {{ c.alergia }}
            </div>

            <dl class="totales">
              <div><dt>Subtotal</dt><dd>{{ colones(c.subtotal) }}</dd></div>
              <div><dt>IVA (13 %)</dt><dd>{{ colones(c.iva) }}</dd></div>
              <div><dt>Servicio (10 %)</dt><dd>{{ colones(c.servicio) }}</dd></div>
              <div class="gran"><dt>Total</dt><dd>{{ colones(c.total) }}</dd></div>
            </dl>

            <div class="acciones">
              <ion-button size="small" fill="outline" :disabled="c.estado === 'servida'" @click="avisar(`Comanda #${c.codigo} enviada a cocina (demostración)`)">
                Enviar a cocina
              </ion-button>
              <ion-button size="small" @click="avisar(`Cobro de ${colones(c.total)} registrado (demostración)`)">Cobrar</ion-button>
            </div>
          </div>
        </article>

        <p v-if="visibles.length === 0" class="vacio">No hay comandas con ese estado.</p>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { IonBadge, IonButton, IonContent, IonIcon, IonPage, onIonViewWillEnter, toastController } from '@ionic/vue';
import { timeOutline, warningOutline } from 'ionicons/icons';
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import AppHeader from '@/components/AppHeader.vue';
import { colones, comandas, ETIQUETA_ESTADO, MINUTOS_RETRASO, type EstadoComanda } from '@/services/datosDemo';

const route = useRoute();
const todas = comandas();
const filtros: Array<{ valor: '' | EstadoComanda; texto: string }> = [
  { valor: '', texto: 'Todas' },
  { valor: 'abierta', texto: 'Abiertas' },
  { valor: 'en_preparacion', texto: 'En prep.' },
  { valor: 'servida', texto: 'Servidas' },
];
const filtro = ref<'' | EstadoComanda>('');
const abierta = ref<string | null>(todas[0]?.codigo ?? null);

const visibles = computed(() => todas.filter((c) => !filtro.value || c.estado === filtro.value));
const atrasadas = todas.filter((c) => c.retraso);
const total = todas.reduce((s, c) => s + c.total, 0);
const cuenta = (estado: EstadoComanda): number => todas.filter((c) => c.estado === estado).length;

function alternar(codigo: string): void {
  abierta.value = abierta.value === codigo ? null : codigo;
}

// Viene de "Ver" en una mesa: abre esa comanda.
onIonViewWillEnter(() => {
  const pedida = route.query.abrir;
  if (typeof pedida === 'string' && todas.some((c) => c.codigo === pedida)) {
    filtro.value = '';
    abierta.value = pedida;
  }
});

async function avisar(mensaje: string): Promise<void> {
  const toast = await toastController.create({ message: mensaje, duration: 2200, position: 'bottom', positionAnchor: 'barra-inferior' });
  await toast.present();
}
</script>

<style scoped>
.contenedor-ancho { max-width: 900px; margin: 0 auto; }

.resumen { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 12px; }
.dato { background: #fff; border: 1px solid var(--ba-borde); border-radius: 12px; padding: 10px 12px; display: flex; flex-direction: column; gap: 2px; }
.dato span { font-size: 12px; color: var(--ion-color-medium); }
.dato strong { font-size: 22px; line-height: 1.1; }
.dato--ancho { grid-column: 1 / -1; background: var(--ion-color-primary); border-color: var(--ion-color-primary); }
.dato--ancho span { color: rgb(255 255 255 / 0.75); }
.dato--ancho strong.dinero { font-family: var(--ba-titulo); color: #fff; font-size: 26px; }
.aviso { color: #b45309; }

.alerta { display: flex; gap: 8px; align-items: flex-start; padding: 10px 12px; margin-bottom: 12px; border: 1px solid #f1b8b2; background: #fdecea; color: #b42318; border-radius: 8px; font-size: 13px; }
.alerta ion-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

.chips { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 12px; }
.chip { flex-shrink: 0; padding: 7px 12px; border-radius: 8px; border: 1px solid var(--ba-borde); background: #fff; font: inherit; font-size: 13px; font-weight: 500; color: var(--ion-text-color); }
.chip[aria-pressed='true'] { background: var(--ion-color-primary); border-color: var(--ion-color-primary); color: #fff; }

.comanda { background: #fff; border: 1.5px solid var(--ba-borde); border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
.comanda--retraso { border-color: #e7a19a; }
.comanda--abierta { border-color: var(--ion-color-primary); }
.cabecera { display: flex; flex-direction: column; gap: 4px; width: 100%; padding: 12px 14px; background: none; border: 0; text-align: left; font: inherit; color: inherit; }
.fila { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.mesa-titulo { font-family: var(--ba-titulo); font-size: 19px; font-weight: 700; }
.sub { font-size: 12px; color: var(--ion-color-medium); }
.tiempo { display: inline-flex; align-items: center; gap: 4px; font-size: 12.5px; color: var(--ion-color-medium); }
.comanda--retraso .tiempo { color: #b42318; font-weight: 600; }
.total { font-size: 17px; font-weight: 700; }

.detalle { padding: 0 14px 14px; border-top: 1px solid var(--ba-borde); }
.items { list-style: none; margin: 0; padding: 0; }
.items li { display: grid; grid-template-columns: 30px 1fr auto; gap: 6px; padding: 10px 0; border-bottom: 1px solid var(--ba-borde); font-size: 14px; }
.cant { color: var(--ion-color-medium); font-weight: 600; }
.nom { font-weight: 600; display: flex; flex-direction: column; gap: 3px; align-items: flex-start; }
.nota { font-weight: 400; background: #fdf0e3; color: #b45309; padding: 1px 7px; border-radius: 4px; }
.pre { font-weight: 600; }

.alergia { display: flex; gap: 6px; align-items: center; margin-top: 10px; padding: 8px 10px; background: #fdf0e3; color: #b45309; border-radius: 8px; font-size: 13px; }

.totales { margin: 12px 0 0; padding: 10px 12px; background: var(--ba-fondo-suave); border-radius: 10px; display: grid; gap: 4px; }
.totales div { display: flex; justify-content: space-between; margin: 0; font-size: 13px; color: var(--ion-color-medium); }
.totales dt, .totales dd { margin: 0; }
.totales .gran { margin-top: 4px; padding-top: 6px; border-top: 1px solid var(--ba-borde); color: var(--ion-text-color); align-items: baseline; font-weight: 600; }
.totales .gran dd { font-family: var(--ba-titulo); font-size: 22px; color: var(--ion-color-primary); }

.acciones { display: flex; gap: 8px; justify-content: flex-end; margin-top: 12px; }
.vacio { text-align: center; color: var(--ion-color-medium); padding: 24px 0; }
</style>
