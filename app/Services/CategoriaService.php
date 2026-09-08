<?php

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Categoria;

class CategoriaService
{
    /**
     * Elimina una categoría, siempre que no tenga productos asociados.
     *
     * La base de datos ya protege esto a nivel de esquema (restrictOnDelete() en
     * productos.categoria_id, ver database/migrations), pero sin este chequeo la aplicación
     * dejaría que el error crudo de SQL llegara hasta el cliente. Verificarlo acá primero permite
     * responder un 409 con un mensaje en español, en vez de una excepción de base de datos.
     */
    public function eliminar(Categoria $categoria): void
    {
        if ($categoria->productos()->exists()) {
            throw new ReglaNegocioException('No se puede eliminar una categoría que tiene productos asociados.');
        }

        $categoria->delete();
    }
}
