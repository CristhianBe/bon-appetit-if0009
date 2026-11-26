import { IonicVue } from '@ionic/vue';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { registrarManejadorNoAutenticado } from './services/http';
import { completarRol } from './services/authApi';
import { cargarSesion } from './services/sesion';

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

import './theme/variables.css';

async function arrancar(): Promise<void> {
  await cargarSesion();
  await completarRol();

  registrarManejadorNoAutenticado(async () => {
    if (router.currentRoute.value.name !== 'login') {
      await router.replace({ name: 'login', query: { expirada: '1' } });
    }
  });

  const app = createApp(App)
    .use(IonicVue, { hardwareBackButton: false })
    .use(router);

  await router.isReady();
  app.mount('#app');
}

arrancar();
