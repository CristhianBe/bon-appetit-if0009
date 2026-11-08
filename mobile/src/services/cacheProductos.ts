import { Network } from '@capacitor/network';
import { Preferences } from '@capacitor/preferences';
import type { Producto } from './productosApi';

/**
 * Caché del último listado consultado. Va en Preferences (sin cifrar) porque el menú
 * es información pública; el token, en cambio, vive en almacenSeguro.ts.
 */
const CLAVE_CACHE = 'bonappetit.cache.productos';

export interface CacheProductos {
  productos: Producto[];
  pagina: number;
  ultimaPagina: number;
  /** ISO 8601 de la última actualización exitosa. */
  actualizadoEn: string;
}

export async function guardarCacheProductos(cache: Omit<CacheProductos, 'actualizadoEn'>): Promise<void> {
  const completo: CacheProductos = { ...cache, actualizadoEn: new Date().toISOString() };
  await Preferences.set({ key: CLAVE_CACHE, value: JSON.stringify(completo) });
}

export async function leerCacheProductos(): Promise<CacheProductos | null> {
  const { value } = await Preferences.get({ key: CLAVE_CACHE });
  if (!value) {
    return null;
  }
  try {
    return JSON.parse(value) as CacheProductos;
  } catch {
    return null;
  }
}

export async function hayConexion(): Promise<boolean> {
  const estado = await Network.getStatus();
  return estado.connected;
}

export function formatearFechaHora(iso: string): string {
  return new Intl.DateTimeFormat('es-CR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(iso));
}
