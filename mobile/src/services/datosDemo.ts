/**
 * Datos de demostración de Mesas y Comandas. Son los mismos que usa el panel web
 * (App\Support\DatosDemo en Laravel), para que web y app cuenten la misma historia.
 * Todavía no hay endpoints de mesas/comandas consumidos por la app.
 */

export type EstadoComanda = 'abierta' | 'en_preparacion' | 'servida';
export type EstadoMesa = 'libre' | 'ocupada' | 'reservada';

export interface ItemComanda {
  cant: number;
  nombre: string;
  precio: number;
  nota?: string;
}

export interface Comanda {
  codigo: string;
  mesa: number;
  personas: number;
  mesero: string;
  minutos: number;
  estado: EstadoComanda;
  alergia?: string;
  items: ItemComanda[];
  subtotal: number;
  iva: number;
  servicio: number;
  total: number;
  cantidadItems: number;
  retraso: boolean;
}

export interface Reserva {
  nombre: string;
  personas: number;
  hora: string;
}

export interface Mesa {
  numero: number;
  zona: 'Interior' | 'Terraza' | 'Barra';
  capacidad: number;
  estado: EstadoMesa;
  comanda: Comanda | null;
  reserva: Reserva | null;
}

export const IVA = 0.13;
export const SERVICIO = 0.1;
export const MINUTOS_RETRASO = 45;

type Base = Omit<Comanda, 'subtotal' | 'iva' | 'servicio' | 'total' | 'cantidadItems' | 'retraso'>;

const BASE: Base[] = [
  {
    codigo: 'C-0142', mesa: 2, personas: 4, mesero: 'Ana M.', minutos: 42, estado: 'en_preparacion', alergia: 'Nueces',
    items: [
      { cant: 2, nombre: 'Casado con pollo', precio: 4500, nota: 'Sin cebolla' },
      { cant: 1, nombre: 'Arroz con camarones', precio: 5200 },
      { cant: 3, nombre: 'Refresco natural', precio: 1200 },
      { cant: 1, nombre: 'Cheesecake', precio: 2500 },
    ],
  },
  {
    codigo: 'C-0143', mesa: 3, personas: 6, mesero: 'Luis M.', minutos: 65, estado: 'en_preparacion',
    items: [
      { cant: 3, nombre: 'Arroz con camarones', precio: 5200, nota: 'Uno sin picante' },
      { cant: 2, nombre: 'Nachos con queso', precio: 3200 },
      { cant: 4, nombre: 'Refresco natural', precio: 1200 },
    ],
  },
  {
    codigo: 'C-0146', mesa: 6, personas: 2, mesero: 'Ana M.', minutos: 18, estado: 'abierta',
    items: [
      { cant: 1, nombre: 'Ensalada César', precio: 3000 },
      { cant: 1, nombre: 'Casado con pollo', precio: 4500 },
      { cant: 2, nombre: 'Refresco natural', precio: 1200 },
    ],
  },
  {
    codigo: 'C-0147', mesa: 7, personas: 4, mesero: 'Luis M.', minutos: 33, estado: 'servida',
    items: [
      { cant: 2, nombre: 'Nachos con queso', precio: 3200 },
      { cant: 2, nombre: 'Casado con pollo', precio: 4500 },
      { cant: 2, nombre: 'Refresco natural', precio: 1200 },
    ],
  },
  {
    codigo: 'C-0149', mesa: 9, personas: 3, mesero: 'Carla R.', minutos: 27, estado: 'servida',
    items: [
      { cant: 2, nombre: 'Ensalada César', precio: 3000 },
      { cant: 1, nombre: 'Arroz con camarones', precio: 5200 },
      { cant: 3, nombre: 'Refresco natural', precio: 1200 },
    ],
  },
  {
    codigo: 'C-0152', mesa: 12, personas: 2, mesero: 'Carla R.', minutos: 55, estado: 'en_preparacion',
    items: [
      { cant: 1, nombre: 'Arroz con camarones', precio: 5200 },
      { cant: 1, nombre: 'Casado con pollo', precio: 4500 },
      { cant: 2, nombre: 'Refresco natural', precio: 1200 },
    ],
  },
];

/** Formato de colones de Costa Rica: ₡38.500 */
export function colones(monto: number): string {
  return `₡${Math.round(monto).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
}

export const ETIQUETA_ESTADO: Record<EstadoComanda, { texto: string; color: string }> = {
  abierta: { texto: 'Abierta', color: 'tertiary' },
  en_preparacion: { texto: 'En preparación', color: 'warning' },
  servida: { texto: 'Servida', color: 'success' },
};

export function comandas(): Comanda[] {
  const lista: Comanda[] = BASE.map((c) => {
    const subtotal = c.items.reduce((s, i) => s + i.cant * i.precio, 0);
    const iva = Math.round(subtotal * IVA);
    const servicio = Math.round(subtotal * SERVICIO);
    return {
      ...c,
      subtotal,
      iva,
      servicio,
      total: subtotal + iva + servicio,
      cantidadItems: c.items.reduce((s, i) => s + i.cant, 0),
      retraso: c.estado !== 'servida' && c.minutos > MINUTOS_RETRASO,
    };
  });
  // Primero las atrasadas y, dentro de cada grupo, la más antigua arriba.
  return lista.sort((a, b) => Number(b.retraso) - Number(a.retraso) || b.minutos - a.minutos);
}

const RESERVAS: Record<number, Reserva> = {
  4: { nombre: 'Fam. Rodríguez', personas: 4, hora: '20:30' },
  10: { nombre: 'Grupo Vargas', personas: 8, hora: '21:00' },
};

const SALON: Array<[number, Mesa['zona'], number]> = [
  [1, 'Interior', 4], [2, 'Interior', 4], [3, 'Interior', 6], [4, 'Interior', 4],
  [5, 'Interior', 2], [6, 'Interior', 2], [7, 'Terraza', 4], [8, 'Terraza', 6],
  [9, 'Terraza', 4], [10, 'Terraza', 8], [11, 'Barra', 2], [12, 'Barra', 2],
];

export function mesas(): Mesa[] {
  const porMesa = new Map(comandas().map((c) => [c.mesa, c]));
  return SALON.map(([numero, zona, capacidad]) => {
    const comanda = porMesa.get(numero) ?? null;
    const reserva = !comanda ? (RESERVAS[numero] ?? null) : null;
    const estado: EstadoMesa = comanda ? 'ocupada' : reserva ? 'reservada' : 'libre';
    return { numero, zona, capacidad, estado, comanda, reserva };
  });
}
