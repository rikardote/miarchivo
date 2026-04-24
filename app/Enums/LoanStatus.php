<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Reserved = 'reserved';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobado',
            self::Reserved => 'Reservado',
            self::Delivered => 'Entregado',
            self::Returned => 'Devuelto',
            self::Rejected => 'Rechazado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Reserved => 'secondary',
            self::Delivered => 'primary',
            self::Returned => 'success',
            self::Rejected => 'error',
            self::Cancelled => 'neutral',
        };
    }
}
