import { http } from './http';
import { actualizarUsuario, cerrarSesion, haySesionActiva, iniciarSesion, usuarioActual, type Usuario } from './sesion';

interface RespuestaLogin {
  token: string;
  user: Usuario;
}

export async function login(email: string, password: string): Promise<Usuario> {
  const { data } = await http.post<RespuestaLogin>('/login', { email, password });
  await iniciarSesion(data.token, data.user);
  return data.user;
}

export async function logout(): Promise<void> {
  try {
    await http.post('/logout');
  } catch {
  } finally {
    await cerrarSesion();
  }
}

export async function completarRol(): Promise<void> {
  const actual = usuarioActual.value;
  if (!haySesionActiva() || !actual || actual.rol) return;
  try {
    const { data } = await http.get<Usuario>('/user');
    await actualizarUsuario({ ...actual, ...data });
  } catch {
  }
}
