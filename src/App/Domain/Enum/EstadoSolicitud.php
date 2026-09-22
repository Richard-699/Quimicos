<?php

namespace App\Domain\Enum;

enum EstadoSolicitud: int
{
    case APROBADO = 1;
    case PENDIENTE = 3;
}