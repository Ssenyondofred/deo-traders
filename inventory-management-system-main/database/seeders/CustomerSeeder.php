<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['code' => 'CUS-001', 'name' => 'PT Peternakan Sejahtera', 'payment_term_days' => 14],
            ['code' => 'CUS-002', 'name' => 'UD Ternak Maju', 'payment_term_days' => 7],
            ['code' => 'CUS-003', 'name' => 'CV Agro Farm Nusantara', 'payment_term_days' => 30],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['code' => $customer['code']],
                array_merge($customer, ['is_active' => true])
            );
        }
    }
}
