<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SaleBomRule;
use Illuminate\Database\Seeder;

class SaleBomSeeder extends Seeder
{
    public function run(): void
    {
        $finished = Product::where('code', 'FG-CORN')->first();
        if (! $finished) {
            return;
        }

        $rules = [
            'PK-SACK' => 0.02,
            'PK-ROPE' => 0.001,
            'RM-SOLAR' => 0.005,
        ];

        foreach ($rules as $code => $qtyPerUnit) {
            $component = Product::where('code', $code)->first();
            if (! $component) {
                continue;
            }

            SaleBomRule::firstOrCreate(
                [
                    'finished_product_id' => $finished->id,
                    'component_product_id' => $component->id,
                ],
                ['qty_per_unit' => $qtyPerUnit, 'is_active' => true]
            );
        }
    }
}
