<template>
  <ion-page>
    <ion-content class="contenido-seguro login-fondo">
      <!-- Franja de marca, como el panel vino del login web -->
      <section class="marca" aria-label="Bon Appétit">
        <span class="logo"><ion-icon :icon="restaurantOutline" aria-hidden="true" /></span>
        <p class="nombre">Bon Appétit</p>
        <span class="linea" aria-hidden="true"></span>
        <p class="lema">Gestión de mesas, comandas y menú</p>
      </section>

      <div class="contenedor login">
        <div class="saludo">
          <h1>Bienvenido de nuevo</h1>
          <ion-text color="medium">
            <p>Iniciá sesión con tu cuenta del restaurante.</p>
          </ion-text>
        </div>

        <ion-card v-if="sesionExpirada" color="warning">
          <ion-card-content>Tu sesión venció. Volvé a iniciar sesión para continuar.</ion-card-content>
        </ion-card>

        <form novalidate @submit.prevent="enviar">
          <ion-list lines="full" class="tarjeta-form">
            <ion-item>
              <ion-input
                v-model="email"
                type="email"
                inputmode="email"
                autocomplete="email"
                label="Correo electrónico"
                label-placement="floating"
                :class="{ 'ion-invalid ion-touched': errores.email }"
                :error-text="errores.email"
                :disabled="enviando"
                required
              />
            </ion-item>
            <ion-item>
              <ion-input
                v-model="password"
                type="password"
                autocomplete="current-password"
                label="Contraseña"
                label-placement="floating"
                :class="{ 'ion-invalid ion-touched': errores.password }"
                :error-text="errores.password"
                :disabled="enviando"
                required
              >
                <ion-input-password-toggle slot="end" />
              </ion-input>
            </ion-item>
          </ion-list>

          <ion-card v-if="mensajeError" color="danger" role="alert">
            <ion-card-content class="mensaje-error">
              <ion-icon :icon="alertCircleOutline" aria-hidden="true" />
              <span>{{ mensajeError }}</span>
            </ion-card-content>
          </ion-card>

          <ion-button type="submit" expand="block" class="ion-margin-top" :disabled="enviando">
            <ion-spinner v-if="enviando" name="crescent" />
            <span v-else>Iniciar sesión</span>
          </ion-button>
        </form>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import {
  IonButton,
  IonCard,
  IonCardContent,
  IonContent,
  IonIcon,
  IonInput,
  IonInputPasswordToggle,
  IonItem,
  IonList,
  IonPage,
  IonSpinner,
  IonText,
} from '@ionic/vue';
import { alertCircleOutline, restaurantOutline } from 'ionicons/icons';
import { computed, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { login } from '@/services/authApi';
import { normalizarError } from '@/services/errores';

const route = useRoute();
const router = useRouter();

const email = ref('');
const password = ref('');
const enviando = ref(false);
const mensajeError = ref('');
const errores = reactive<{ email?: string; password?: string }>({});

const sesionExpirada = computed(() => route.query.expirada === '1' && !mensajeError.value);

/** Mismas reglas que IniciarSesionRequest: email requerido y con formato; password requerido. */
function validar(): boolean {
  errores.email = undefined;
  errores.password = undefined;

  if (!email.value.trim()) {
    errores.email = 'El correo electrónico es obligatorio.';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    errores.email = 'El correo electrónico no tiene un formato válido.';
  }
  if (!password.value) {
    errores.password = 'La contraseña es obligatoria.';
  }

  return !errores.email && !errores.password;
}

async function enviar(): Promise<void> {
  mensajeError.value = '';
  if (!validar()) {
    return;
  }

  enviando.value = true;
  try {
    await login(email.value.trim(), password.value);
    password.value = '';

    // replace (no push): la entrada del login se sustituye en el historial, así el
    // botón atrás de Android nunca vuelve a esta pantalla después de autenticarse.
    const destino = typeof route.query.redirect === 'string' ? route.query.redirect : '/mesas';
    await router.replace(destino);
  } catch (error) {
    const normalizado = normalizarError(error);
    if (normalizado.status === 429) {
      mensajeError.value = 'Demasiados intentos. Esperá un minuto antes de volver a intentar.';
    } else {
      // 401 -> "Credenciales incorrectas." | 422 -> errores por campo | sin red.
      mensajeError.value = normalizado.mensaje;
      errores.email = normalizado.errores.email;
      errores.password = normalizado.errores.password;
    }
  } finally {
    enviando.value = false;
  }
}
</script>

<style scoped>
/* La franja de marca ocupa todo el ancho, por encima del padding del contenido. */
.marca {
  margin: calc(-16px - var(--ion-safe-area-top, 0px)) calc(-16px - var(--ion-safe-area-right, 0px)) 24px calc(-16px - var(--ion-safe-area-left, 0px));
  padding: calc(40px + var(--ion-safe-area-top, 0px)) 24px 32px;
  background: var(--ion-color-primary);
  color: #fff;
  text-align: center;
}
.logo {
  display: inline-grid;
  place-items: center;
  width: 56px;
  height: 56px;
  border: 1.5px solid var(--ion-color-secondary);
  border-radius: 50%;
  color: var(--ion-color-secondary);
  font-size: 26px;
}
.nombre { margin: 12px 0 0; font-family: var(--ba-titulo); font-size: 32px; font-weight: 700; }
.linea { display: block; width: 44px; height: 2px; margin: 10px auto; background: var(--ion-color-secondary); border-radius: 2px; }
.lema { margin: 0; color: rgb(255 255 255 / 0.8); font-size: 14px; }

.login { max-width: 420px; }
.saludo h1 { margin: 0 0 4px; font-size: 26px; }
.saludo p { margin: 0 0 16px; }

.tarjeta-form {
  border: 1px solid var(--ba-borde);
  border-radius: 12px;
  overflow: hidden;
  padding: 0;
}

.mensaje-error {
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
