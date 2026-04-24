<?php

namespace App\Enums;

enum ExpedientStatus: string
{
    case Available = 'available';
    case Requested = 'requested';
    case Reserved = 'reserved';
    case Loaned = 'loaned';
    case Returned = 'returned';
    case Archived = 'archived';
    case InStorage = 'in_storage';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Requested => 'Solicitado',
            self::Reserved => 'Reservado',
            self::Loaned => 'Prestado',
            self::Returned => 'Devuelto',
            self::Archived => 'Archivado',
            self::InStorage => 'En almacén',
            self::Lost => 'Extraviado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Requested => 'warning',
            self::Reserved => 'info',
            self::Loaned => 'primary',
            self::Returned => 'accent',
            self::Archived => 'neutral',
            self::InStorage => 'secondary',
            self::Lost => 'error',
        };
    }
}
