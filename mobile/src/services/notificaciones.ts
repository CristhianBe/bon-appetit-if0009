import { Capacitor } from '@capacitor/core';
import { LocalNotifications } from '@capacitor/local-notifications';

export type ResultadoPermiso = 'concedido' | 'denegado' | 'denegado-permanente' | 'no-soportado';

/**
 * Pide el permiso de notificaciones en tiempo de ejecución (Android 13+ exige
 * POST_NOTIFICATIONS). Así mapea Capacitor el estado nativo:
 *  - 'prompt' / 'prompt-with-rationale': todavía se puede mostrar el diálogo del sistema.
 *  - 'denied': el usuario marcó "No volver a preguntar" (o lo bloqueó desde Ajustes).
 *    El sistema ya no muestra el diálogo; solo se puede habilitar a mano en Ajustes.
 */
export async function asegurarPermisoNotificaciones(): Promise<ResultadoPermiso> {
  if (!Capacitor.isPluginAvailable('LocalNotifications')) {
    return 'no-soportado';
  }

  const actual = await LocalNotifications.checkPermissions();
  if (actual.display === 'granted') {
    return 'concedido';
  }
  if (actual.display === 'denied') {
    return 'denegado-permanente';
  }

  const pedido = await LocalNotifications.requestPermissions();
  if (pedido.display === 'granted') {
    return 'concedido';
  }

  // Después del primer rechazo Android permite volver a preguntar ('prompt-with-rationale').
  // Si ahora el estado es 'denied', el usuario eligió que no se le vuelva a consultar.
  const despues = await LocalNotifications.checkPermissions();
  return despues.display === 'denied' ? 'denegado-permanente' : 'denegado';
}

export const INSTRUCCIONES_HABILITAR =
  'Las notificaciones están bloqueadas para Bon Appetit. Para activarlas, abrí Ajustes del ' +
  'teléfono → Aplicaciones → Bon Appetit → Notificaciones y activá "Permitir notificaciones".';

/** Avisa con una notificación local que un producto se guardó en el servidor. */
export async function notificarProductoGuardado(nombre: string, esNuevo: boolean): Promise<void> {
  await LocalNotifications.schedule({
    notifications: [
      {
        id: Math.floor(Date.now() % 2147483647),
        title: esNuevo ? 'Producto creado' : 'Producto actualizado',
        body: `"${nombre}" se guardó correctamente en el menú.`,
      },
    ],
  });
}
