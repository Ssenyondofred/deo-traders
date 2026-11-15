<?php

namespace App\Enums;

enum ApStatus: string
{
    case Open = 'open';
    case Partial = 'partial';
    case Paid = 'paid';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Belum Lunas',
            self::Partial => 'Cicilan',
            self::Paid => 'Lunas',
            self::Void => 'Batal',
        };
    }
}
