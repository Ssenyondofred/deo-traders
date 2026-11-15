<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CoaSeeder::class,
            AccountMappingSeeder::class,
            MasterDataSeeder::class,
            StockBalanceSeeder::class,
            UserSeeder::class,
        ]);
    }
}
