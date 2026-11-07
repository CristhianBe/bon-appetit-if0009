<template>
  <ion-page>
    <ion-header>
      <ion-toolbar color="primary">
        <ion-title>Bon Appetit</ion-title>
      </ion-toolbar>
    </ion-header>

    <ion-content class="contenido-seguro">
      <div class="contenedor login">
        <div class="marca">
          <ion-icon :icon="restaurantOutline" color="primary" aria-hidden="true" />
          <h1>Iniciar sesión</h1>
          <ion-text color="medium">
            <p>Ingresá con tu cuenta del restaurante.</p>
          </ion-text>
        </div>

        <ion-card v-if="sesionExpirada" color="warning">
          <ion-card-content>Tu sesión venció. Volvé a iniciar sesión para continuar.</ion-card-content>
        </ion-card>

        <form novalidate @submit.prevent="enviar">
          <ion-list lines="full">
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
            <span v-else>Entrar</span>
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
  IonHeader,
  IonIcon,
  IonInput,
  IonInputPasswordToggle,
  IonItem,
  IonList,
  IonPage,
  IonSpinner,
  IonText,
  IonTitle,
  IonToolbar,
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
    const destino = typeof route.query.redirect === 'string' ? route.query.redirect : '/productos';
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
.login {
  max-width: 420px;
  padding-top: 24px;
}

.marca {
  text-align: center;
  margin-bottom: 16px;
}

.marca ion-icon {
  font-size: 72px;
}

.mensaje-error {
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
