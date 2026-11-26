<template>
  <ion-page>
    <AppHeader titulo="Comandas activas" />

    <ion-content class="contenido-seguro">
      <ion-refresher slot="fixed" @ion-refresh="refrescar">
        <ion-refresher-content />
      </ion-refresher>

      <div class="contenedor-ancho">
        <div v-if="cargando && !todas.length" aria-busy="true">
          <ion-skeleton-text v-for="n in 4" :key="n" animated class="esqueleto" />
        </div>

        <EstadoError
          v-else-if="error && !todas.length"
          :mensaje="error.mensaje"
          :sin-conexion="error.sinConexion"
          @reintentar="cargar"
        />

        <EstadoVacio
          v-else-if="!todas.length"
          titulo="No hay comandas activas"
          :mensaje="tieneRol('cajero') ? 'Cuando un mesero abra una mesa aparecerá aquí para cobrar.' : 'Abrí una comanda desde Mesas para empezar.'"
        >
          <ion-button @click="router.replace({ name: 'mesas' })">Ir a Mesas</ion-button>
        </EstadoVacio>

        <template v-else>
          <!-- Resumen -->
          <section class="resumen" aria-label="Resumen de comandas">
            <div class="dato"><span>Activas</span><strong>{{ todas.length }}</strong></div>
            <div class="dato"><span>En preparación</span><strong class="aviso">{{ cuenta('en_preparacion') }}</strong></div>
            <div class="dato dato--ancho"><span>Facturación en curso</span><strong class="dinero">{{ colones(totalGeneral) }}</strong></div>
          </section>

          <div v-if="atrasadas.length" class="alerta" role="alert">
            <ion-icon :icon="warningOutline" aria-hidden="true" />
            <span>
              {{ atrasadas.length }} {{ atrasadas.length === 1 ? 'comanda supera' : 'comandas superan' }} los {{ MINUTOS_RETRASO }} min:
              {{ atrasadas.map((c) => `Mesa ${c.mesa?.numero} (${minutos(c)} min)`).join(', ') }}.
            </span>
          </div>

          <!-- Filtro por estado -->
          <div class="chips" role="group" aria-label="Filtrar por estado">
            <button v-for="f in FILTROS" :key="f.valor" type="button" class="chip" :aria-pressed="filtro === f.valor" @click="filtro = f.valor">
              {{ f.texto }} ({{ f.valor ? cuenta(f.valor) : todas.length }})
            </button>
          </div>

          <!-- Lista -->
          <article
            v-for="c in visibles"
            :key="c.id"
            :class="['comanda', { 'comanda--retraso': conRetraso(c), 'comanda--abierta': abierta === c.id }]"
          >
            <button type="button" class="cabecera" :aria-expanded="abierta === c.id" @click="abierta = abierta === c.id ? null : c.id">
              <span class="fila">
                <span class="mesa-titulo">Mesa {{ c.mesa?.numero }}</span>
                <ion-badge :color="info(c).color">{{ info(c).texto }}</ion-badge>
              </span>
              <span class="sub">#{{ c.id }} · {{ c.mesero?.name ?? '—' }} · {{ cantidadItems(c) }} productos</span>
              <span class="fila">
                <span class="tiempo"><ion-icon :icon="timeOutline" aria-hidden="true" /> {{ duracion(minutos(c)) }}</span>
                <span class="total">{{ colones(c.total_calculado) }}</span>
              </span>
            </button>

            <div v-if="abierta === c.id" class="detalle">
              <ul class="items">
                <li v-for="d in c.detalles" :key="d.id">
                  <span class="cant">{{ d.cantidad }}×</span>
                  <span class="nom">
                    {{ d.producto?.nombre ?? `Producto #${d.producto_id}` }}
                    <small v-if="d.notas" class="nota">{{ d.notas }}</small>
                    <span v-if="puedeEditar(c)" class="controles">
                      <button type="button" :disabled="ocupado" :aria-label="`Quitar una unidad de ${d.producto?.nombre}`" @click="ajustar(c, d.id, -1)">−</button>
                      <button type="button" :disabled="ocupado" :aria-label="`Agregar una unidad de ${d.producto?.nombre}`" @click="ajustar(c, d.id, 1)">+</button>
                      <button type="button" class="quitar" :disabled="ocupado" @click="quitar(c, d.id)">Quitar</button>
                    </span>
                  </span>
                  <span class="pre">{{ colones(d.subtotal) }}</span>
                </li>
                <li v-if="!c.detalles?.length" class="sin-items">Todavía no hay productos en esta comanda.</li>
              </ul>

              <div v-if="c.notas" class="alergia">
                <ion-icon :icon="warningOutline" aria-hidden="true" /> <strong>Notas:</strong> {{ c.notas }}
              </div>

              <dl class="totales">
                <div><dt>Subtotal</dt><dd>{{ colones(c.subtotal) }}</dd></div>
                <div v-if="(c.descuento ?? 0) > 0"><dt>Descuento (10 %)</dt><dd>− {{ colones(c.descuento) }}</dd></div>
                <div class="gran"><dt>Total</dt><dd>{{ colones(c.total_calculado) }}</dd></div>
              </dl>

              <div class="acciones">
                <template v-if="puedeEditar(c)">
                  <ion-button size="small" fill="outline" :disabled="ocupado" @click="abrirSelector(c)">
                    <ion-icon slot="start" :icon="addOutline" aria-hidden="true" /> Productos
                  </ion-button>
                  <ion-button v-if="codigo(c) === 'abierta'" size="small" fill="outline" :disabled="ocupado || !c.detalles?.length" @click="cambiarEstado(c, 'en_preparacion')">
                    Enviar a cocina
                  </ion-button>
                  <ion-button v-else-if="codigo(c) === 'en_preparacion'" size="small" fill="outline" :disabled="ocupado" @click="cambiarEstado(c, 'servida')">
                    Marcar servida
                  </ion-button>
                </template>
                <ion-button v-if="puedeCobrar" size="small" :disabled="ocupado || !c.detalles?.length" @click="cobrar(c)">Cobrar</ion-button>
              </div>
              <p v-if="!puedeEditar(c) && !puedeCobrar" class="solo-lectura">Solo lectura: esta comanda la atiende otro mesero.</p>
              <button v-if="puedeEditar(c)" type="button" class="cancelar" :disabled="ocupado" @click="cancelar(c)">Cancelar esta comanda</button>
            </div>
          </article>

          <p v-if="visibles.length === 0" class="vacio">No hay comandas con ese estado.</p>
        </template>
      </div>

      <!-- Selector de productos -->
      <ion-modal :is-open="selector.abierto" @did-dismiss="selector.abierto = false">
        <ion-header>
          <ion-toolbar color="primary">
            <ion-title>Agregar productos</ion-title>
            <ion-buttons slot="end">
              <ion-button @click="selector.abierto = false">Listo</ion-button>
            </ion-buttons>
          </ion-toolbar>
          <ion-toolbar>
            <ion-searchbar v-model="selector.texto" placeholder="Buscar platillo o bebida" :debounce="150" />
          </ion-toolbar>
        </ion-header>
        <ion-content class="ion-padding">
          <ion-input
            v-model="selector.nota"
            label="Nota para el próximo producto (opcional)"
            label-placement="stacked"
            fill="outline"
            placeholder="Sin cebolla, término medio…"
            :maxlength="255"
          />
          <p class="resumen-selector">En la comanda: {{ comandaSelector ? cantidadItems(comandaSelector) : 0 }} productos · {{ colones(comandaSelector?.total_calculado) }}</p>
          <div v-if="!productos.length" class="estado-centrado"><ion-spinner /></div>
          <ion-list v-else lines="full">
            <ion-item v-for="p in productosFiltrados" :key="p.id">
              <ion-label>
                <h3>{{ p.nombre }}</h3>
                <p>{{ p.categoria?.nombre }} · {{ colones(p.precio) }}</p>
              </ion-label>
              <ion-button slot="end" size="small" :disabled="ocupado" @click="agregar(p.id)">Añadir</ion-button>
            </ion-item>
          </ion-list>
        </ion-content>
      </ion-modal>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import {
  alertController, IonBadge, IonButton, IonButtons, IonContent, IonHeader, IonIcon, IonInput, IonItem, IonLabel,
  IonList, IonModal, IonPage, IonRefresher, IonRefresherContent, IonSearchbar, IonSkeletonText, IonSpinner, IonTitle,
  IonToolbar, onIonViewDidLeave, onIonViewWillEnter, toastController,
} from '@ionic/vue';
import { addOutline, timeOutline, warningOutline } from 'ionicons/icons';
import { computed, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppHeader from '@/components/AppHeader.vue';
import EstadoError from '@/components/EstadoError.vue';
import EstadoVacio from '@/components/EstadoVacio.vue';
import { normalizarError, type ErrorNormalizado } from '@/services/errores';
import { colones, duracion, minutosDesde, MINUTOS_RETRASO } from '@/services/formato';
import type { Producto } from '@/services/productosApi';
import {
  agregarDetalle, cambiarCantidad, cambiarEstadoComanda, cancelarComanda, cobrarComanda, listarComandasActivas,
  listarEstadosComanda, listarMetodosPago, listarProductosDisponibles, obtenerComanda, quitarDetalle,
  type Catalogo, type CodigoEstadoComanda, type Comanda,
} from '@/services/salonApi';
import { tieneRol, usuarioActual } from '@/services/sesion';

type Filtro = '' | 'abierta' | 'en_preparacion' | 'servida';
const FILTROS: Array<{ valor: Filtro; texto: string }> = [
  { valor: '', texto: 'Todas' },
  { valor: 'abierta', texto: 'Abiertas' },
  { valor: 'en_preparacion', texto: 'En prep.' },
  { valor: 'servida', texto: 'Servidas' },
];
const ETIQUETAS: Record<string, { texto: string; color: string }> = {
  abierta: { texto: 'Abierta', color: 'tertiary' },
  en_preparacion: { texto: 'En preparación', color: 'warning' },
  servida: { texto: 'Servida', color: 'success' },
};

const route = useRoute();
const router = useRouter();

const todas = ref<Comanda[]>([]);
const cargando = ref(false);
const ocupado = ref(false);
const error = ref<ErrorNormalizado | null>(null);
const filtro = ref<Filtro>('');
const abierta = ref<number | null>(null);
const estadosId = ref<Record<string, number>>({});
const metodos = ref<Catalogo[]>([]);
const productos = ref<Producto[]>([]);
const selector = reactive({ abierto: false, comandaId: 0, texto: '', nota: '' });

const puedeCobrar = computed(() => tieneRol('administrador', 'cajero'));
const puedeEditar = (c: Comanda): boolean => tieneRol('administrador') || (tieneRol('mesero') && c.user_id === usuarioActual.value?.id);

const codigo = (c: Comanda): CodigoEstadoComanda => c.estado?.codigo ?? 'abierta';
const minutos = (c: Comanda): number => minutosDesde(c.creado_en);
const conRetraso = (c: Comanda): boolean => codigo(c) !== 'servida' && minutos(c) > MINUTOS_RETRASO;
const cantidadItems = (c: Comanda): number => (c.detalles ?? []).reduce((n, d) => n + d.cantidad, 0);
const info = (c: Comanda) => (conRetraso(c) ? { texto: 'Retraso', color: 'danger' } : ETIQUETAS[codigo(c)] ?? { texto: codigo(c), color: 'medium' });

const visibles = computed(() => todas.value.filter((c) => !filtro.value || codigo(c) === filtro.value));
const atrasadas = computed(() => todas.value.filter(conRetraso));
const totalGeneral = computed(() => todas.value.reduce((s, c) => s + (c.total_calculado ?? 0), 0));
const cuenta = (estado: Filtro): number => todas.value.filter((c) => codigo(c) === estado).length;

const comandaSelector = computed(() => todas.value.find((c) => c.id === selector.comandaId));
const productosFiltrados = computed(() => {
  const t = selector.texto.trim().toLowerCase();
  return productos.value.filter((p) => !t || `${p.nombre} ${p.categoria?.nombre ?? ''}`.toLowerCase().includes(t));
});

async function cargar(): Promise<void> {
  cargando.value = true;
  try {
    const [comandas, estados, listaMetodos] = await Promise.all([
      listarComandasActivas(),
      Object.keys(estadosId.value).length ? Promise.resolve(null) : listarEstadosComanda(),
      metodos.value.length ? Promise.resolve(null) : listarMetodosPago(),
    ]);
    todas.value = comandas;
    if (estados) estadosId.value = Object.fromEntries(estados.map((e) => [e.codigo, e.id]));
    if (listaMetodos) metodos.value = listaMetodos;
    error.value = null;

    // Viene de "Ver comanda" en Mesas: se abre esa.
    const pedida = Number(route.query.abrir);
    if (pedida && todas.value.some((c) => c.id === pedida)) {
      filtro.value = '';
      abierta.value = pedida;
    } else if (!todas.value.some((c) => c.id === abierta.value)) {
      abierta.value = todas.value[0]?.id ?? null;
    }
  } catch (e) {
    error.value = normalizarError(e);
    if (todas.value.length) await avisar(error.value.mensaje);
  } finally {
    cargando.value = false;
  }
}

async function refrescar(evento: CustomEvent): Promise<void> {
  await cargar();
  (evento.target as HTMLIonRefresherElement).complete();
}

async function recargarUna(id: number): Promise<void> {
  const comanda = await obtenerComanda(id);
  const i = todas.value.findIndex((c) => c.id === id);
  if (i >= 0) todas.value[i] = comanda;
}

/** Ejecuta una acción contra la API mostrando el error del servidor si falla. */
async function ejecutar(accion: () => Promise<void>): Promise<void> {
  ocupado.value = true;
  try {
    await accion();
  } catch (e) {
    const err = normalizarError(e);
    await avisar(err.status === 403 ? 'Tu rol no tiene permiso para hacer esto.' : err.mensaje);
    if (err.status === 404 || err.status === 409) await cargar();
  } finally {
    ocupado.value = false;
  }
}

function ajustar(c: Comanda, detalleId: number, delta: number): Promise<void> {
  return ejecutar(async () => {
    const d = c.detalles?.find((x) => x.id === detalleId);
    if (!d) return;
    if (d.cantidad + delta < 1) await quitarDetalle(detalleId);
    else await cambiarCantidad(detalleId, d.cantidad + delta);
    await recargarUna(c.id);
  });
}

function quitar(c: Comanda, detalleId: number): Promise<void> {
  return ejecutar(async () => {
    await quitarDetalle(detalleId);
    await recargarUna(c.id);
  });
}

function cambiarEstado(c: Comanda, nuevo: 'en_preparacion' | 'servida'): Promise<void> {
  return ejecutar(async () => {
    await cambiarEstadoComanda(c.id, estadosId.value[nuevo]);
    await recargarUna(c.id);
    await avisar(nuevo === 'servida' ? `Comanda #${c.id} servida.` : `Comanda #${c.id} enviada a cocina.`);
  });
}

async function abrirSelector(c: Comanda): Promise<void> {
  Object.assign(selector, { abierto: true, comandaId: c.id, texto: '', nota: '' });
  if (!productos.value.length) {
    try {
      productos.value = await listarProductosDisponibles();
    } catch (e) {
      selector.abierto = false;
      await avisar(normalizarError(e).mensaje);
    }
  }
}

function agregar(productoId: number): Promise<void> {
  return ejecutar(async () => {
    const c = comandaSelector.value;
    if (!c) return;
    const nota = selector.nota.trim();
    // Mismo producto sin nota: se suma a la línea existente en vez de duplicarla.
    const existente = !nota ? c.detalles?.find((d) => d.producto_id === productoId && !d.notas) : undefined;
    if (existente) await cambiarCantidad(existente.id, existente.cantidad + 1);
    else await agregarDetalle(c.id, productoId, 1, nota || null);
    selector.nota = '';
    await recargarUna(c.id);
  });
}

async function cobrar(c: Comanda): Promise<void> {
  const alerta = await alertController.create({
    header: `Cobrar ${colones(c.total_calculado)}`,
    subHeader: `Mesa ${c.mesa?.numero} · comanda #${c.id}`,
    message: (c.descuento ?? 0) > 0 ? `Incluye descuento de ${colones(c.descuento)}.` : undefined,
    inputs: metodos.value.map((m, i) => ({ type: 'radio' as const, label: m.nombre, value: m.id, checked: i === 0 })),
    buttons: [
      { text: 'Cancelar', role: 'cancel' },
      { text: 'Confirmar cobro', role: 'confirm' },
    ],
  });
  await alerta.present();
  const { role, data } = await alerta.onDidDismiss();
  if (role !== 'confirm' || !data?.values) return;

  await ejecutar(async () => {
    const cerrada = await cobrarComanda(c.id, Number(data.values));
    todas.value = todas.value.filter((x) => x.id !== c.id);
    abierta.value = todas.value[0]?.id ?? null;
    await avisar(`Cobro de ${colones(cerrada.total)} registrado (${cerrada.metodo_pago?.nombre ?? 'pago'}). Mesa ${c.mesa?.numero} libre.`);
  });
}

async function cancelar(c: Comanda): Promise<void> {
  const alerta = await alertController.create({
    header: `Cancelar la comanda #${c.id}`,
    message: `Se borra con todos sus productos y la mesa ${c.mesa?.numero} queda libre. No se puede deshacer.`,
    buttons: [
      { text: 'Volver', role: 'cancel' },
      { text: 'Cancelar comanda', role: 'destructive' },
    ],
  });
  await alerta.present();
  const { role } = await alerta.onDidDismiss();
  if (role !== 'destructive') return;

  await ejecutar(async () => {
    await cancelarComanda(c.id);
    todas.value = todas.value.filter((x) => x.id !== c.id);
    abierta.value = todas.value[0]?.id ?? null;
    await avisar(`Comanda #${c.id} cancelada.`);
  });
}

async function avisar(mensaje: string): Promise<void> {
  const toast = await toastController.create({ message: mensaje, duration: 2600, position: 'bottom', positionAnchor: 'barra-inferior' });
  await toast.present();
}

let temporizador: ReturnType<typeof setInterval> | undefined;
onIonViewWillEnter(() => {
  cargar();
  temporizador = setInterval(() => { if (!document.hidden && !selector.abierto && !ocupado.value) cargar(); }, 30000);
});
onIonViewDidLeave(() => clearInterval(temporizador));
</script>

<style scoped>
.contenedor-ancho { max-width: 900px; margin: 0 auto; }
.esqueleto { height: 96px; border-radius: 12px; margin: 0 0 10px; }

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
.chip { flex-shrink: 0; min-height: 36px; padding: 7px 12px; border-radius: 8px; border: 1px solid var(--ba-borde); background: #fff; font: inherit; font-size: 13px; font-weight: 500; color: var(--ion-text-color); }
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
.items li.sin-items { display: block; color: var(--ion-color-medium); }
.cant { color: var(--ion-color-medium); font-weight: 600; }
.nom { font-weight: 600; display: flex; flex-direction: column; gap: 4px; align-items: flex-start; }
.nota { font-weight: 400; background: #fdf0e3; color: #b45309; padding: 1px 7px; border-radius: 4px; }
.pre { font-weight: 600; }
.controles { display: flex; gap: 6px; margin-top: 2px; }
.controles button { min-width: 36px; min-height: 32px; padding: 0 10px; border: 1px solid var(--ba-borde); border-radius: 6px; background: #fff; font: inherit; font-weight: 600; color: var(--ion-text-color); }
.controles button.quitar { color: #b42318; font-weight: 500; font-size: 12.5px; }
.controles button:disabled { opacity: .5; }

.alergia { display: flex; gap: 6px; align-items: center; margin-top: 10px; padding: 8px 10px; background: #fdf0e3; color: #b45309; border-radius: 8px; font-size: 13px; }

.totales { margin: 12px 0 0; padding: 10px 12px; background: var(--ba-fondo-suave); border-radius: 10px; display: grid; gap: 4px; }
.totales div { display: flex; justify-content: space-between; margin: 0; font-size: 13px; color: var(--ion-color-medium); }
.totales dt, .totales dd { margin: 0; }
.totales .gran { margin-top: 4px; padding-top: 6px; border-top: 1px solid var(--ba-borde); color: var(--ion-text-color); align-items: baseline; font-weight: 600; }
.totales .gran dd { font-family: var(--ba-titulo); font-size: 22px; color: var(--ion-color-primary); }

.acciones { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; margin-top: 12px; }
.solo-lectura { margin: 10px 0 0; font-size: 13px; color: var(--ion-color-medium); }
.cancelar { display: block; margin: 12px auto 0; background: none; border: 0; color: #b42318; font: inherit; font-size: 13px; text-decoration: underline; min-height: 36px; }
.vacio { text-align: center; color: var(--ion-color-medium); padding: 24px 0; }
.resumen-selector { margin: 12px 0 4px; font-size: 13px; color: var(--ion-color-medium); }
</style>
