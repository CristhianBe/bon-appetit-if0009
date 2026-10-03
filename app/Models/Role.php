<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

// Extiende el Role de spatie/laravel-permission en vez de reemplazarlo: así se conservan
// gratis assignRole()/hasRole()/etc. (definidos en el paquete) y se le agrega SOLO lo propio
// de este proyecto ("descripcion", que el paquete no trae de fábrica). Está registrado como el
// modelo de rol oficial en config/permission.php ('models.role' => App\Models\Role::class),
// así que $user->assignRole(...) y compañía usan ESTA clase, no la del paquete directamente.
class Role extends SpatieRole
{
    use HasFactory;

    // 'name' y 'guard_name' ya vienen protegidos como fillable en el Role del paquete;
    // acá solo se agrega el campo propio.
    protected $fillable = ['name', 'guard_name', 'descripcion'];
}
