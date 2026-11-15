<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CashBankAccount;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'PT Jagung Nusantara', 'payment_term_days' => 14],
            ['code' => 'SUP-002', 'name' => 'UD Sumber Pangan', 'payment_term_days' => 7],
            ['code' => 'SUP-003', 'name' => 'CV Tani Makmur', 'payment_term_days' => 30],
        ];
        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['code' => $supplier['code']], array_merge($supplier, ['is_active' => true]));
        }

        Warehouse::create([
            'code' => 'GDG-01',
            'name' => 'Gudang Utama',
            'address' => 'Gudang utama perusahaan',
            'is_default' => true,
            'is_active' => true,
        ]);

        $cashAccounts = [
            ['code' => 'CASH-KECIL', 'name' => 'Kas Kecil', 'account' => '1001.01'],
            ['code' => 'CASH-BESAR', 'name' => 'Kas Besar', 'account' => '1001.02'],
            ['code' => 'BANK-MDR', 'name' => 'Bank Mandiri', 'account' => '1002.01', 'bank' => 'Mandiri', 'number' => '1680069000'],
            ['code' => 'BANK-BCA', 'name' => 'Bank BCA', 'account' => '1002.02', 'bank' => 'BCA', 'number' => '8650858523'],
        ];

        foreach ($cashAccounts as $row) {
            $accountId = Account::where('code', $row['account'])->value('id');
            $coa = Account::find($accountId);

            CashBankAccount::create([
                'account_id' => $accountId,
                'code' => $row['code'],
                'name' => $row['name'],
                'bank_name' => $row['bank'] ?? null,
                'account_number' => $row['number'] ?? null,
                'balance' => $coa?->opening_balance ?? 0,
                'is_active' => true,
            ]);
        }

        $products = [
            ['code' => 'RM-CORN', 'name' => 'Jagung Mentah', 'category' => 'raw_material', 'unit' => 'Kg'],
            ['code' => 'FG-CORN', 'name' => 'Jagung Kering', 'category' => 'finished_goods', 'unit' => 'Kg'],
            ['code' => 'RM-COAL', 'name' => 'Batubara', 'category' => 'fuel', 'unit' => 'Kg'],
            ['code' => 'PK-SACK', 'name' => 'Karung', 'category' => 'packaging', 'unit' => 'Pcs'],
            ['code' => 'PK-ROPE', 'name' => 'Tali', 'category' => 'packaging', 'unit' => 'Roll'],
            ['code' => 'RM-SOLAR', 'name' => 'Solar', 'category' => 'fuel', 'unit' => 'Liter'],
        ];

        foreach ($products as $product) {
            Product::create(array_merge($product, [
                'default_price' => 0,
                'avg_cost' => 0,
                'track_stock' => true,
                'is_active' => true,
            ]));
        }

        if (StockBalance::count() === 0) {
            app(InventoryService::class)->initializeBalances();
        }
    }
}
