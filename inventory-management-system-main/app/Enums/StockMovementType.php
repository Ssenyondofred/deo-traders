<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Opening = 'opening';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case PurchaseIn = 'purchase_in';
    case SaleOut = 'sale_out';
    case ProductionIn = 'production_in';
    case ProductionOut = 'production_out';

    public function label(): string
    {
        return match ($this) {
            self::Opening => 'Saldo Awal',
            self::AdjustmentIn => 'Penyesuaian Masuk',
            self::AdjustmentOut => 'Penyesuaian Keluar',
            self::PurchaseIn => 'Pembelian',
            self::SaleOut => 'Penjualan',
            self::ProductionIn => 'Hasil Produksi',
            self::ProductionOut => 'Bahan Produksi',
        };
    }

    public function direction(): string
    {
        return match ($this) {
            self::AdjustmentOut, self::SaleOut, self::ProductionOut => 'out',
            default => 'in',
        };
    }
}
