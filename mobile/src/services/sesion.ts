import { computed, reactive } from 'vue';
import { borrarSeguro, guardarSeguro, leerSeguro } from './almacenSeguro';

export interface Usuario {
  id: number;
  name: string;
  email: string;
}

interface SesionGuardada {
  token: string;
  usuario: Usuario;
  /** Epoch en ms en que vence el token. */
  expiraEn: number;
}

const CLAVE_SESION = 'bonappetit.sesion';

/** Vida del token según AuthController@login: expiresAt = now()->addMinutes(30). */
export const DURACION_TOKEN_MS = 30 * 60 * 1000;

const estado = reactive<{ token: string | null; usuario: Usuario | null; expiraEn: number | null }>({
  token: null,
  usuario: null,
  expiraEn: null,
});

export const usuarioActual = computed(() => estado.usuario);

export function haySesionActiva(): boolean {
  return !!estado.token && !!estado.expiraEn && Date.now() < estado.expiraEn;
}

/** Token vigente en memoria. Lo usa SOLO el interceptor de request de http.ts. */
export function tokenActual(): string | null {
  return haySesionActiva() ? estado.token : null;
}

/** Se llama una vez al arrancar la app, antes de montar el router. */
export async function cargarSesion(): Promise<void> {
  const crudo = await leerSeguro(CLAVE_SESION);
  if (!crudo) {
    return;
  }
  try {
    const guardada = JSON.parse(crudo) as SesionGuardada;
    if (Date.now() >= guardada.expiraEn) {
      await cerrarSesion();
      return;
    }
    estado.token = guardada.token;
    estado.usuario = guardada.usuario;
    estado.expiraEn = guardada.expiraEn;
  } catch {
    await cerrarSesion();
  }
}

export async function iniciarSesion(token: string, usuario: Usuario): Promise<void> {
  const sesion: SesionGuardada = { token, usuario, expiraEn: Date.now() + DURACION_TOKEN_MS };
  await guardarSeguro(CLAVE_SESION, JSON.stringify(sesion));
  estado.token = sesion.token;
  estado.usuario = sesion.usuario;
  estado.expiraEn = sesion.expiraEn;
}

export async function cerrarSesion(): Promise<void> {
  estado.token = null;
  estado.usuario = null;
  estado.expiraEn = null;
  await borrarSeguro(CLAVE_SESION);
}
