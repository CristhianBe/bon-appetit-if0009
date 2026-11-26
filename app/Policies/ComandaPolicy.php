<?php

namespace App\Policies;

use App\Models\Comanda;
use App\Models\User;

class ComandaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Comanda $comanda): bool
    {
        return $user->hasRole('administrador')
            || $user->hasRole('cajero')
            || ($user->hasRole('mesero') && $comanda->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('administrador') || $user->hasRole('mesero');
    }

    public function update(User $user, Comanda $comanda): bool
    {
        return $user->hasRole('administrador')
            || ($user->hasRole('mesero') && $comanda->user_id === $user->id);
    }

    public function delete(User $user, Comanda $comanda): bool
    {
        return $user->hasRole('administrador')
            || ($user->hasRole('mesero') && $comanda->user_id === $user->id);
    }

    public function cobrar(User $user, Comanda $comanda): bool
    {
        return $user->hasRole('administrador') || $user->hasRole('cajero');
    }
}
