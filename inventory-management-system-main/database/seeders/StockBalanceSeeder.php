<?php

namespace Database\Seeders;

use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class StockBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $count = app(InventoryService::class)->initializeBalances();
        $this->command?->info("Stock balances initialized: {$count} products.");
    }
}
