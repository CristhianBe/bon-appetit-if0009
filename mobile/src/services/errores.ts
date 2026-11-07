import { isAxiosError } from 'axios';

/** Forma uniforme de los errores del backend (ver bootstrap/app.php). */
interface ErrorApi {
  message?: string;
  codigo?: string;
  errors?: Record<string, string[]>;
}

export interface ErrorNormalizado {
  mensaje: string;
  status: number | null;
  codigo: string | null;
  /** Primer error por campo cuando codigo = validacion_fallida (422). */
  errores: Record<string, string>;
  /** true si no hubo respuesta del servidor (sin red, servidor caído, timeout). */
  sinConexion: boolean;
}

export function normalizarError(error: unknown): ErrorNormalizado {
  if (isAxiosError<ErrorApi>(error)) {
    if (!error.response) {
      return {
        mensaje: 'No se pudo conectar con el servidor. Revisá tu conexión e intentá de nuevo.',
        status: null,
        codigo: null,
        errores: {},
        sinConexion: true,
      };
    }

    const datos = error.response.data ?? {};
    const errores: Record<string, string> = {};
    for (const [campo, mensajes] of Object.entries(datos.errors ?? {})) {
      errores[campo] = mensajes[0];
    }

    return {
      mensaje: datos.message ?? `Error inesperado (${error.response.status}).`,
      status: error.response.status,
      codigo: datos.codigo ?? null,
      errores,
      sinConexion: false,
    };
  }

  return {
    mensaje: 'Ocurrió un error inesperado.',
    status: null,
    codigo: null,
    errores: {},
    sinConexion: false,
  };
}
