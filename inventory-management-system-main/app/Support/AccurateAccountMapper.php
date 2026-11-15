<?php

namespace App\Support;

use App\Enums\AccountType;

class AccurateAccountMapper
{
    public static function map(string $accurateType): array
    {
        return match ($accurateType) {
            'Kas/Bank' => [AccountType::CashBank, 'debit'],
            'Akun Piutang' => [AccountType::Receivable, 'debit'],
            'Persediaan' => [AccountType::Inventory, 'debit'],
            'Aktiva Lancar lainnya' => [AccountType::CurrentAsset, 'debit'],
            'Aktiva Tetap' => [AccountType::FixedAsset, 'debit'],
            'Akumulasi Penyusutan' => [AccountType::AccumulatedDepreciation, 'credit'],
            'Akun Hutang' => [AccountType::Payable, 'credit'],
            'Hutang lancar lainnya' => [AccountType::CurrentLiability, 'credit'],
            'Ekuitas' => [AccountType::Equity, 'credit'],
            'Pendapatan' => [AccountType::Revenue, 'credit'],
            'Harga Pokok Penjualan' => [AccountType::Cogs, 'debit'],
            'Beban' => [AccountType::Expense, 'debit'],
            'Pendapatan lain' => [AccountType::OtherIncome, 'credit'],
            'Beban lain-lain' => [AccountType::OtherExpense, 'debit'],
            default => [AccountType::CurrentAsset, 'debit'],
        };
    }
}
