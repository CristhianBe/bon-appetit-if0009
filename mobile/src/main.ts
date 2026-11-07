import { IonicVue } from '@ionic/vue';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { registrarManejadorNoAutenticado } from './services/http';
import { cargarSesion } from './services/sesion';

/* CSS base de Ionic */
import '@ionic/vue/css/core.css';
import '@ionic/vue/css/normalize.css';
import '@ionic/vue/css/structure.css';
import '@ionic/vue/css/typography.css';
import '@ionic/vue/css/padding.css';
import '@ionic/vue/css/float-elements.css';
import '@ionic/vue/css/text-alignment.css';
import '@ionic/vue/css/text-transformation.css';
import '@ionic/vue/css/flex-utils.css';
import '@ionic/vue/css/display.css';
import '@ionic/vue/css/palettes/dark.system.css';

import './theme/variables.css';

async function arrancar(): Promise<void> {
  // El token se lee del almacenamiento cifrado ANTES de la primera navegación, para que
  // el guardián del router ya sepa si hay sesión.
  await cargarSesion();

  // 401 en cualquier llamada (token vencido o revocado): el interceptor ya borró la
  // sesión; acá se manda al login reemplazando la entrada actual del historial.
  registrarManejadorNoAutenticado(async () => {
    if (router.currentRoute.value.name !== 'login') {
      await router.replace({ name: 'login', query: { expirada: '1' } });
    }
  });

  const app = createApp(App)
    // El botón atrás físico se maneja a mano en App.vue con el plugin App de Capacitor.
    .use(IonicVue, { hardwareBackButton: false })
    .use(router);

  await router.isReady();
  app.mount('#app');
}

arrancar();
