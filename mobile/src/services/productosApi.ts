import { ref } from 'vue';
import { http } from './http';

export const listadoDesactualizado = ref(false);

export interface Categoria {
  id: number;
  nombre: string;
  descripcion: string | null;
}

export interface Producto {
  id: number;
  categoria_id: number;
  nombre: string;
  descripcion: string | null;
  precio: string | number;
  disponible: boolean;
  imagen_url: string | null;
  categoria?: Categoria;
  created_at: string;
  updated_at: string;
}

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

export async function listarProductos(pagina: number): Promise<Paginado<Producto>> {
  const { data } = await http.get<Paginado<Producto>>('/productos', {
    params: { page: pagina, por_pagina: TAMANO_PAGINA },
  });
  return data;
}

export async function obtenerProducto(id: number): Promise<Producto> {
  const { data } = await http.get<{ data: Producto }>(`/productos/${id}`);
  return data.data;
}

export async function crearProducto(payload: ProductoPayload): Promise<Producto> {
  const { data } = await http.post<{ data: Producto }>('/productos', payload);
  return data.data;
}

export async function actualizarProducto(id: number, payload: ProductoPayload): Promise<Producto> {
  const { data } = await http.put<{ data: Producto }>(`/productos/${id}`, payload);
  return data.data;
}

export async function listarCategorias(): Promise<Categoria[]> {
  const { data } = await http.get<Paginado<Categoria>>('/categorias', {
    params: { por_pagina: 50 },
  });
  return data.data;
}

export function formatearPrecio(precio: string | number): string {
  return new Intl.NumberFormat('es-CR', { style: 'currency', currency: 'CRC' }).format(Number(precio));
}
