import { createRouter, createWebHistory } from '@ionic/vue-router';
import type { RouteRecordRaw } from 'vue-router';
import { haySesionActiva } from '@/services/sesion';

declare module 'vue-router' {
  interface RouteMeta {
    publica?: boolean;
  }
}

const routes: RouteRecordRaw[] = [
  { path: '/', redirect: '/mesas' },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginPage.vue'),
    meta: { publica: true },
  },
  {
    path: '/mesas',
    name: 'mesas',
    component: () => import('@/views/MesasPage.vue'),
  },
  {
    path: '/comandas',
    name: 'comandas',
    component: () => import('@/views/ComandasPage.vue'),
  },
  {
    path: '/productos',
    name: 'productos',
    component: () => import('@/views/ProductosPage.vue'),
  },
  {
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
  {
    path: '/administracion',
    name: 'administracion',
    component: () => import('@/views/administracion.vue'),
  },
  { path: '/:pathMatch(.*)*', redirect: '/mesas' },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

router.beforeEach((to) => {
  const conSesion = haySesionActiva();

  if (!to.meta.publica && !conSesion) {
    return {
      name: 'login',
      query: to.fullPath !== '/mesas' ? { redirect: to.fullPath } : {},
      replace: true,
    };
  }

  if (to.name === 'login' && conSesion) {
    return { name: 'mesas', replace: true };
  }

  return true;
});

export default router;
