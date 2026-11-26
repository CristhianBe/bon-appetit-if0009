import { computed, reactive } from 'vue';
import { borrarSeguro, guardarSeguro, leerSeguro } from './almacenSeguro';

export type Rol = 'administrador' | 'mesero' | 'cajero';

export interface Usuario {
  id: number;
  name: string;
  email: string;
  rol?: Rol | null;
}

interface SesionGuardada {
  token: string;
  usuario: Usuario;
  expiraEn: number;
}

const CLAVE_SESION = 'bonappetit.sesion';

export const DURACION_TOKEN_MS = 30 * 60 * 1000;

const estado = reactive<{ token: string | null; usuario: Usuario | null; expiraEn: number | null }>({
  token: null,
  usuario: null,
  expiraEn: null,
});

export const usuarioActual = computed(() => estado.usuario);
export const rolActual = computed(() => estado.usuario?.rol ?? null);

export function tieneRol(...roles: Rol[]): boolean {
  return !!rolActual.value && roles.includes(rolActual.value);
}

export const ETIQUETA_ROL: Record<Rol, string> = {
  administrador: 'Administrador',
  mesero: 'Mesero',
  cajero: 'Cajero',
};

export function haySesionActiva(): boolean {
  return !!estado.token && !!estado.expiraEn && Date.now() < estado.expiraEn;
}

export function tokenActual(): string | null {
  return haySesionActiva() ? estado.token : null;
}

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

export async function actualizarUsuario(usuario: Usuario): Promise<void> {
  if (!estado.token || !estado.expiraEn) return;
  const sesion: SesionGuardada = { token: estado.token, usuario, expiraEn: estado.expiraEn };
  await guardarSeguro(CLAVE_SESION, JSON.stringify(sesion));
  estado.usuario = usuario;
}

export async function cerrarSesion(): Promise<void> {
  estado.token = null;
  estado.usuario = null;
  estado.expiraEn = null;
  await borrarSeguro(CLAVE_SESION);
}
