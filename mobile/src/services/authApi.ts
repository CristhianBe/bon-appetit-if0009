import { http } from './http';
import { actualizarUsuario, cerrarSesion, haySesionActiva, iniciarSesion, usuarioActual, type Usuario } from './sesion';

interface RespuestaLogin {
  token: string;
  user: Usuario;
}

/** POST /api/login {email, password} -> {token, user:{id,name,email,rol}} */
export async function login(email: string, password: string): Promise<Usuario> {
  const { data } = await http.post<RespuestaLogin>('/login', { email, password });
  await iniciarSesion(data.token, data.user);
  return data.user;
}

/** POST /api/logout revoca el token en el servidor; localmente se borra siempre. */
export async function logout(): Promise<void> {
  try {
    await http.post('/logout');
  } catch {
    // Sin red o token ya vencido: igual se cierra la sesión local.
  } finally {
    await cerrarSesion();
  }
}

/**
 * Las sesiones guardadas antes de que el login devolviera el rol no lo traen: se pide una vez
 * a GET /api/user. Si falla (sin red), la app sigue y solo muestra lo de lectura.
 */
export async function completarRol(): Promise<void> {
  const actual = usuarioActual.value;
  if (!haySesionActiva() || !actual || actual.rol) return;
  try {
    const { data } = await http.get<Usuario>('/user');
    await actualizarUsuario({ ...actual, ...data });
  } catch {
    /* sin conexión: se reintenta en el próximo arranque */
  }
}
