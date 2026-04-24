<?php

namespace App\Enums;

enum MovementType: string
{
    case Created = 'created';
    case Loaned = 'loaned';
    case Returned = 'returned';
    case Relocated = 'relocated';
    case StatusChanged = 'status_changed';
    case Lost = 'lost';
    case Found = 'found';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Creado',
            self::Loaned => 'Prestado',
            self::Returned => 'Devuelto',
            self::Relocated => 'Reubicado',
            self::StatusChanged => 'Cambio de estado',
            self::Lost => 'Extraviado',
            self::Found => 'Encontrado',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Created => 'o-plus-circle',
            self::Loaned => 'o-arrow-up-tray',
            self::Returned => 'o-arrow-down-tray',
            self::Relocated => 'o-arrows-right-left',
            self::StatusChanged => 'o-arrow-path',
            self::Lost => 'o-exclamation-triangle',
            self::Found => 'o-magnifying-glass',
        };
    }
}
