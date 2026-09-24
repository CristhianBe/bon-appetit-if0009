<?php

namespace App\Policies;

use App\Models\Comanda;
use App\Models\User;

// Reglas de autorización para Comanda (Laboratorio 6):
//   - administrador → ve, abre, edita y elimina CUALQUIER comanda.
//   - mesero        → abre comandas, pero solo ve/edita/elimina las que ÉL abrió
//                      (comanda->user_id, ver Comanda::mesero()).
//   - cajero        → solo lectura de CUALQUIER comanda (necesita verlas para cobrar/cerrar
//                      caja), no puede abrir/editar/eliminar.
//   - sin rol       → no puede hacer nada (ni siquiera leer).
class ComandaPolicy
{
    /**
     * ¿Puede listar comandas (GET /api/comandas)?
     * Cualquier usuario autenticado puede listar; el filtrado fino por dueño ocurre en
     * view(), al pedir el detalle de UNA comanda en concreto.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * ¿Puede ver el detalle de ESTA comanda (GET /api/comandas/{id})?
     */
    public function view(User $user, Comanda $comanda): bool
    {
        return $user->hasRole('administrador')
            || $user->hasRole('cajero')
            || ($user->hasRole('mesero') && $comanda->user_id === $user->id);
    }

    /**
     * ¿Puede abrir una comanda nueva (POST /api/comandas)?
     * No hay "dueño" todavía (la comanda no existe), así que acá solo se chequea el rol:
     * cajero no abre mesas, por lo tanto no crea comandas.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('administrador') || $user->hasRole('mesero');
    }

    /**
     * ¿Puede editar ESTA comanda (PUT /api/comandas/{id}), incluyendo cerrarla?
     * Igual que view(), pero sin el rol de solo lectura: cajero puede VER, no EDITAR.
     */
    public function update(User $user, Comanda $comanda): bool
    {
        return $user->hasRole('administrador')
            || ($user->hasRole('mesero') && $comanda->user_id === $user->id);
    }

    /**
     * ¿Puede eliminar ESTA comanda (DELETE /api/comandas/{id})?
     * Misma regla que update(): administrador, o el mesero dueño de la comanda.
     */
    public function delete(User $user, Comanda $comanda): bool
    {
        return $user->hasRole('administrador')
            || ($user->hasRole('mesero') && $comanda->user_id === $user->id);
    }
}
