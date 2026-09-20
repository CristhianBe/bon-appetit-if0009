<?php

namespace App\Exceptions;

use Exception;

class ReglaNegocioException extends Exception
{
    public function __construct(
        string $mensaje,
        protected string $codigo = 'regla_negocio',
        protected int $statusSugerido = 422,
    ) {
        parent::__construct($mensaje);
    }

    public function codigo(): string
    {
        return $this->codigo;
    }

    public function statusSugerido(): int
    {
        return $this->statusSugerido;
    }
}