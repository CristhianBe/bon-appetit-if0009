import axios, { AxiosError } from 'axios';
import { cerrarSesion, tokenActual } from './sesion';

export const API_URL = import.meta.env.VITE_API_URL ?? 'http://10.0.2.2:8000/api';

/**
 * ÚNICO punto de acceso a la API. Ninguna vista ni servicio arma el header de
 * autenticación a mano: el interceptor de request pone el token y el de response
 * maneja el 401 de forma centralizada.
 */
export const http = axios.create({
  baseURL: API_URL,
  timeout: 15000,
  headers: {
    Accept: 'application/json',
  },
});

http.interceptors.request.use((config) => {
  const token = tokenActual();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

type ManejadorNoAutenticado = () => void | Promise<void>;
let alPerderSesion: ManejadorNoAutenticado = () => {};

/** main.ts registra acá la redirección al login (así este módulo no importa el router). */
export function registrarManejadorNoAutenticado(manejador: ManejadorNoAutenticado): void {
  alPerderSesion = manejador;
}

http.interceptors.response.use(
  (respuesta) => respuesta,
  async (error: AxiosError) => {
    // POST /login responde 401 cuando las credenciales son malas: eso NO es una sesión
    // vencida, lo maneja la pantalla de login mostrando el mensaje.
    const esLogin = error.config?.url?.endsWith('/login') ?? false;

    if (error.response?.status === 401 && !esLogin) {
      await cerrarSesion();
      await alPerderSesion();
    }

    return Promise.reject(error);
  },
);
