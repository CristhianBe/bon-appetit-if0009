import { Capacitor } from '@capacitor/core';
import { LocalNotifications } from '@capacitor/local-notifications';

export type ResultadoPermiso = 'concedido' | 'denegado' | 'denegado-permanente' | 'no-soportado';

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

  const despues = await LocalNotifications.checkPermissions();
  return despues.display === 'denied' ? 'denegado-permanente' : 'denegado';
}

export const INSTRUCCIONES_HABILITAR =
  'Las notificaciones están bloqueadas para Bon Appetit. Para activarlas, abrí Ajustes del ' +
  'teléfono → Aplicaciones → Bon Appetit → Notificaciones y activá "Permitir notificaciones".';

export async function notificarProductoGuardado(nombre: string, esNuevo: boolean): Promise<void> {
  await LocalNotifications.schedule({
    notifications: [
      {
        id: Math.floor(Date.now() % 2147483647),
        title: esNuevo ? 'Producto creado' : 'Producto actualizado',
        body: `"${nombre}" se guardó correctamente en el menú.`,
        isExactNotification: false,
      },
    ],
  });
}
