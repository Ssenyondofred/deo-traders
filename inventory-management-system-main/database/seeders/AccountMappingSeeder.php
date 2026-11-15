<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountMapping;
use Illuminate\Database\Seeder;

class AccountMappingSeeder extends Seeder
{
    public function run(): void
    {
        $map = fn (string $code) => Account::where('code', $code)->value('id');

        $mappings = [
            ['purchase.cash', 'Pembelian tunai', '1200.01', null],
            ['purchase.cash.credit_side', 'Pembelian tunai - sisi kas', null, '1001.01'],
            ['purchase.credit', 'Pembelian kredit', '1200.01', '2000.01'],
            ['sale.cash', 'Penjualan tunai', '1001.01', '4000.01'],
            ['sale.credit', 'Penjualan kredit', '1100.01', '4000.01'],
            ['sale.cogs', 'HPP penjualan', '5000.01', '1200.01'],
            ['production.finish', 'Hasil produksi', '1200.01', '1200.01'],
            ['expense.default', 'Beban umum', '6203.13', '1001.01'],
            ['ap.payment', 'Bayar hutang', '2000.01', '1001.01'],
            ['ar.payment', 'Terima piutang', '1001.01', '1100.01'],
            ['tax.ppn_input', 'PPN Masukan', '1400.06', '2000.01'],
            ['tax.ppn_output', 'PPN Keluaran', '1100.01', '2100.07'],
        ];

        foreach ($mappings as [$event, $desc, $debitCode, $creditCode]) {
            AccountMapping::create([
                'event_code' => $event,
                'description' => $desc,
                'debit_account_id' => $debitCode ? $map($debitCode) : null,
                'credit_account_id' => $creditCode ? $map($creditCode) : null,
                'is_active' => true,
            ]);
        }
    }
}
