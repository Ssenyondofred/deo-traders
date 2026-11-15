<?php

namespace App\Enums;

enum AccountType: string
{
    case CashBank = 'cash_bank';
    case Receivable = 'receivable';
    case Inventory = 'inventory';
    case CurrentAsset = 'current_asset';
    case FixedAsset = 'fixed_asset';
    case AccumulatedDepreciation = 'accumulated_depreciation';
    case Payable = 'payable';
    case CurrentLiability = 'current_liability';
    case Equity = 'equity';
    case Revenue = 'revenue';
    case Cogs = 'cogs';
    case Expense = 'expense';
    case OtherIncome = 'other_income';
    case OtherExpense = 'other_expense';
}
