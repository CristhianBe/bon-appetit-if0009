import { http } from './http';
import { cerrarSesion, iniciarSesion, type Usuario } from './sesion';

interface RespuestaLogin {
  token: string;
  user: Usuario;
}

/** POST /api/login {email, password} -> {token, user:{id,name,email}} */
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
