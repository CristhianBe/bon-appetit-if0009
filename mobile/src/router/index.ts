import { createRouter, createWebHistory } from '@ionic/vue-router';
import type { RouteRecordRaw } from 'vue-router';
import { haySesionActiva } from '@/services/sesion';

declare module 'vue-router' {
  interface RouteMeta {
    /** true = accesible sin sesión. Todo lo demás exige un token vigente. */
    publica?: boolean;
  }
}

const routes: RouteRecordRaw[] = [
  { path: '/', redirect: '/productos' },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginPage.vue'),
    meta: { publica: true },
  },
  {
    path: '/productos',
    name: 'productos',
    component: () => import('@/views/ProductosPage.vue'),
  },
  {
    // Va antes de /productos/:id para que "nuevo" no se interprete como un id.
    path: '/productos/nuevo',
    name: 'producto-nuevo',
    component: () => import('@/views/ProductoFormPage.vue'),
  },
  {
    path: '/productos/:id(\\d+)',
    name: 'producto-detalle',
    component: () => import('@/views/ProductoDetallePage.vue'),
    props: (ruta) => ({ id: Number(ruta.params.id) }),
  },
  {
    path: '/productos/:id(\\d+)/editar',
    name: 'producto-editar',
    component: () => import('@/views/ProductoFormPage.vue'),
    props: (ruta) => ({ id: Number(ruta.params.id) }),
  },
  { path: '/:pathMatch(.*)*', redirect: '/productos' },
  {
  path: '/administracion',
  name: 'Administracion',
  component: () => import('@/views/administracion.vue')},
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

/**
 * Guardián global: sin sesión activa (token presente y no vencido) no se entra a
 * ninguna ruta privada. Con sesión, /login redirige al listado para que ni la
 * navegación ni el botón atrás devuelvan a la pantalla de login.
 */
router.beforeEach((to) => {
  const conSesion = haySesionActiva();

  if (!to.meta.publica && !conSesion) {
    return {
      name: 'login',
      query: to.fullPath !== '/productos' ? { redirect: to.fullPath } : {},
      replace: true,
    };
  }

  if (to.name === 'login' && conSesion) {
    return { name: 'productos', replace: true };
  }

  return true;
});

export default router;
