import { ref } from 'vue';
import { http } from './http';

/**
 * Ionic mantiene vivas en memoria las páginas anteriores del stack. El formulario marca
 * este flag al guardar para que el listado se recargue cuando el usuario vuelva a él.
 */
export const listadoDesactualizado = ref(false);

export interface Categoria {
  id: number;
  nombre: string;
  descripcion: string | null;
}

/** Forma de ProductoResource en el backend. */
export interface Producto {
  id: number;
  categoria_id: number;
  nombre: string;
  descripcion: string | null;
  /** Laravel lo serializa como string decimal (ej. "3500.00"). */
  precio: string | number;
  disponible: boolean;
  imagen_url: string | null;
  categoria?: Categoria;
  created_at: string;
  updated_at: string;
}

/** Payload que validan ProductoStoreRequest / ProductoUpdateRequest. */
export interface ProductoPayload {
  nombre: string;
  descripcion: string | null;
  precio: number;
  categoria_id: number;
  disponible: boolean;
  imagen_url: string | null;
}

export interface Paginado<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export const TAMANO_PAGINA = 15;

/** GET /api/productos (público, paginado). */
export async function listarProductos(pagina: number): Promise<Paginado<Producto>> {
  const { data } = await http.get<Paginado<Producto>>('/productos', {
    params: { page: pagina, por_pagina: TAMANO_PAGINA },
  });
  return data;
}

/** GET /api/productos/{id} (público). */
export async function obtenerProducto(id: number): Promise<Producto> {
  const { data } = await http.get<{ data: Producto }>(`/productos/${id}`);
  return data.data;
}

/** POST /api/productos (requiere token). */
export async function crearProducto(payload: ProductoPayload): Promise<Producto> {
  const { data } = await http.post<{ data: Producto }>('/productos', payload);
  return data.data;
}

/** PUT /api/productos/{id} (requiere token). */
export async function actualizarProducto(id: number, payload: ProductoPayload): Promise<Producto> {
  const { data } = await http.put<{ data: Producto }>(`/productos/${id}`, payload);
  return data.data;
}

/** GET /api/categorias (requiere token, paginado; el backend permite como máximo 50 por página). */
export async function listarCategorias(): Promise<Categoria[]> {
  const { data } = await http.get<Paginado<Categoria>>('/categorias', {
    params: { por_pagina: 50 },
  });
  return data.data;
}

export function formatearPrecio(precio: string | number): string {
  return new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC' }).format(Number(precio));
}
