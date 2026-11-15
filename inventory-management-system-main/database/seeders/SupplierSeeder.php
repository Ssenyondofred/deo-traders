<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'PT Jagung Nusantara', 'payment_term_days' => 14],
            ['code' => 'SUP-002', 'name' => 'UD Sumber Pangan', 'payment_term_days' => 7],
            ['code' => 'SUP-003', 'name' => 'CV Tani Makmur', 'payment_term_days' => 30],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['code' => $supplier['code']],
                array_merge($supplier, ['is_active' => true])
            );
        }
    }
}
