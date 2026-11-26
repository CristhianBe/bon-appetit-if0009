/** Formato de colones de Costa Rica: ₡38.500 (igual que el panel web). */
export function colones(monto: number | string | null | undefined): string {
  return `₡${Math.round(Number(monto ?? 0)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
}

export function minutosDesde(iso: string | null | undefined): number {
  if (!iso) return 0;
  return Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 60000));
}

export function duracion(minutos: number): string {
  if (minutos < 60) return `${minutos} min`;
  return `${Math.floor(minutos / 60)} h ${String(minutos % 60).padStart(2, '0')} min`;
}

/** Fecha local AAAA-MM-DD (la del teléfono, que es la del restaurante). */
export function fechaIso(fecha = new Date()): string {
  return new Date(fecha.getTime() - fecha.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

/** Minutos sin servir a partir de los cuales una comanda se marca con retraso. */
export const MINUTOS_RETRASO = 45;
