<?php

namespace App\Exceptions;

use Exception;

// Excepción "genérica" para cualquier regla de negocio violada (ver ComandaService,
// CategoriaService). No necesita código propio: solo existe para que bootstrap/app.php pueda
// distinguirla de otros errores de PHP y traducirla automáticamente a una respuesta HTTP 409
// con el mensaje del error, sin que cada servicio tenga que preocuparse por códigos HTTP.
class ReglaNegocioException extends Exception
{
    //
}
