<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['dashboard.admin', 'Dashboard Admin', 'dashboard'],
            ['inventory.view', 'Lihat Inventory', 'inventory'],
            ['stock_report.view', 'Laporan Stok', 'inventory'],
            ['sales.view', 'Lihat Penjualan', 'sales'],
            ['sales.create', 'Buat Penjualan', 'sales'],
            ['sales.approve', 'Approve Penjualan', 'sales'],
            ['purchases.view', 'Lihat Pembelian', 'purchases'],
            ['purchases.create', 'Buat Pembelian', 'purchases'],
            ['purchases.approve', 'Approve Pembelian', 'purchases'],
            ['production.view', 'Lihat Produksi', 'production'],
            ['production.create', 'Buat Produksi', 'production'],
            ['production.approve', 'Approve Produksi', 'production'],
            ['expenses.view', 'Lihat Biaya', 'expenses'],
            ['expenses.create', 'Buat Biaya', 'expenses'],
            ['expenses.approve', 'Approve Biaya', 'expenses'],
            ['transactions.review', 'Review Transaksi Harian', 'transactions'],
            ['ap.view', 'Lihat Hutang', 'payable'],
            ['ap.pay', 'Bayar Hutang', 'payable'],
            ['ar.view', 'Lihat Piutang', 'receivable'],
            ['ar.pay', 'Terima Piutang', 'receivable'],
            ['journal.view', 'Lihat Jurnal', 'accounting'],
            ['journal.create', 'Jurnal Manual', 'accounting'],
            ['ledger.view', 'Buku Besar', 'accounting'],
            ['financial.view', 'Laporan Keuangan', 'accounting'],
            ['activity_logs.view', 'Log Aktivitas', 'system'],
            ['masters.manage', 'Kelola Master Data', 'system'],
        ];

        $permissionIds = [];
        foreach ($permissions as [$slug, $name, $module]) {
            $permissionIds[$slug] = Permission::create([
                'slug' => $slug,
                'name' => $name,
                'module' => $module,
            ])->id;
        }

        $admin = Role::create([
            'name' => 'Admin / Owner',
            'slug' => 'admin',
            'description' => 'Akses penuh seluruh modul',
        ]);

        $staff = Role::create([
            'name' => 'Staff',
            'slug' => 'staff',
            'description' => 'Operasional harian',
        ]);

        $admin->permissions()->attach(array_values($permissionIds));

        $staffSlugs = [
            'inventory.view',
            'stock_report.view',
            'sales.view',
            'sales.create',
            'purchases.view',
            'purchases.create',
            'production.view',
            'production.create',
            'expenses.view',
            'expenses.create',
        ];

        $staffIds = array_map(fn ($slug) => $permissionIds[$slug], $staffSlugs);
        $staff->permissions()->attach($staffIds);
    }
}
