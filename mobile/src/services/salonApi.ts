import { http } from './http';
import type { Paginado, Producto } from './productosApi';

export type CodigoEstadoComanda = 'abierta' | 'en_preparacion' | 'servida' | 'cerrada';

export interface ComandaActiva {
  id: number;
  estado: CodigoEstadoComanda | null;
  mesero_id: number;
  mesero: string | null;
  personas: number | null;
  abierta_en: string | null;
  items: number;
  total: number;
}

export interface Mesa {
  id: number;
  numero: number;
  capacidad: number;
  zona: string;
  estado: { id: number; codigo: 'libre' | 'ocupada' | 'reservada'; nombre: string } | null;
  comanda_activa: ComandaActiva | null;
}

export interface Detalle {
  id: number;
  comanda_id: number;
  producto_id: number;
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
  notas: string | null;
  producto?: Producto;
}

export interface Comanda {
  id: number;
  mesa_id: number;
  user_id: number;
  personas: number | null;
  notas: string | null;
  total: number;
  subtotal?: number;
  descuento?: number;
  total_calculado?: number;
  creado_en: string | null;
  mesa?: { id: number; numero: number; zona?: string };
  mesero?: { id: number; name: string };
  estado?: { id: number; codigo: CodigoEstadoComanda; nombre: string };
  metodo_pago?: { id: number; codigo: string; nombre: string } | null;
  detalles?: Detalle[];
}

export interface Reserva {
  id: number;
  mesa_id: number | null;
  nombre: string;
  personas: number;
  hora: string;
  fecha: string;
  estado: 'pendiente' | 'confirmada' | 'cumplida' | 'cancelada';
  notas: string | null;
}

export interface Catalogo {
  id: number;
  codigo: string;
  nombre: string;
}

export async function traerTodo<T>(ruta: string, params: Record<string, unknown> = {}): Promise<T[]> {
  const { data: primera } = await http.get<Paginado<T>>(ruta, { params: { ...params, page: 1 } });
  let datos = primera.data ?? [];
  for (let pagina = 2; pagina <= (primera.meta?.last_page ?? 1); pagina += 1) {
    const { data } = await http.get<Paginado<T>>(ruta, { params: { ...params, page: pagina } });
    datos = datos.concat(data.data ?? []);
  }
  return datos;
}

export const listarMesas = () => traerTodo<Mesa>('/mesas', { por_pagina: 100 });

export const listarReservasDelDia = (fecha: string) => traerTodo<Reserva>('/reservas', { fecha, por_pagina: 100 })
  .then((lista) => lista.filter((r) => r.estado === 'pendiente' || r.estado === 'confirmada'));

export const listarComandasActivas = () => traerTodo<Comanda>('/comandas', { activas: 1, por_pagina: 50 });

export const listarEstadosComanda = () => traerTodo<Catalogo>('/estados-comanda');
export const listarMetodosPago = () => traerTodo<Catalogo>('/metodos-pago');
export const listarProductosDisponibles = () => traerTodo<Producto>('/productos', { por_pagina: 50, solo_disponibles: 1 });

export async function obtenerComanda(id: number): Promise<Comanda> {
  const { data } = await http.get<{ data: Comanda }>(`/comandas/${id}`);
  return data.data;
}

export async function abrirComanda(mesaId: number, userId: number, personas: number, notas: string | null): Promise<Comanda> {
  const { data } = await http.post<{ data: Comanda }>('/comandas', { mesa_id: mesaId, user_id: userId, personas, notas });
  return data.data;
}

export async function cambiarEstadoComanda(id: number, estadoId: number): Promise<void> {
  await http.put(`/comandas/${id}`, { estado_comanda_id: estadoId });
}

export async function cancelarComanda(id: number): Promise<void> {
  await http.delete(`/comandas/${id}`);
}

export async function cobrarComanda(id: number, metodoPagoId: number): Promise<Comanda> {
  const { data } = await http.post<{ data: Comanda }>(`/comandas/${id}/cobrar`, { metodo_pago_id: metodoPagoId });
  return data.data;
}

export async function agregarDetalle(comandaId: number, productoId: number, cantidad: number, notas: string | null): Promise<void> {
  await http.post(`/comandas/${comandaId}/detalles`, { producto_id: productoId, cantidad, notas });
}

export async function cambiarCantidad(detalleId: number, cantidad: number): Promise<void> {
  await http.put(`/detalles/${detalleId}`, { cantidad });
}

export async function quitarDetalle(detalleId: number): Promise<void> {
  await http.delete(`/detalles/${detalleId}`);
}

export async function marcarReservaCumplida(id: number): Promise<void> {
  await http.put(`/reservas/${id}`, { estado: 'cumplida' });
}
