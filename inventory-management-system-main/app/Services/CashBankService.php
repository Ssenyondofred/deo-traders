<?php

namespace App\Services;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CashBankService
{
    public function record(
        CashBankAccount $account,
        string $direction,
        float $amount,
        string $description,
        ?Model $reference = null,
        ?JournalEntry $journalEntry = null,
        \DateTimeInterface|string|null $transactionDate = null,
    ): CashBankTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount harus lebih dari 0.');
        }

        return DB::transaction(function () use ($account, $direction, $amount, $description, $reference, $journalEntry, $transactionDate) {
            $locked = CashBankAccount::where('id', $account->id)->lockForUpdate()->firstOrFail();
            $balanceBefore = (float) $locked->balance;

            if ($direction === 'out' && $balanceBefore < $amount) {
                throw new InvalidArgumentException(
                    'Saldo kas/bank tidak mencukupi. Tersedia: Rp '.number_format($balanceBefore, 0, ',', '.')
                );
            }

            $balanceAfter = $direction === 'in'
                ? $balanceBefore + $amount
                : $balanceBefore - $amount;

            $locked->update(['balance' => $balanceAfter]);

            return CashBankTransaction::create([
                'cash_bank_account_id' => $locked->id,
                'transaction_date' => $transactionDate ? \Carbon\Carbon::parse($transactionDate) : now(),
                'direction' => $direction,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'journal_entry_id' => $journalEntry?->id,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
