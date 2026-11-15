<?php

namespace App\Services;

use App\Models\ArDocument;
use App\Models\ArPayment;
use App\Models\CashBankAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ArPaymentService
{
    public function __construct(
        private JournalService   $journalService,
        private CashBankService  $cashBankService,
        private DocumentNumberService $docNumber,
    ) {}

    public function pay(ArDocument $ar, array $data): ArPayment
{
    return DB::transaction(function () use ($ar, $data) {
        $amount = (float) $data['amount'];

        if ($amount > $ar->balance_amount) {
            throw new \Exception('Jumlah pembayaran melebihi sisa piutang.');
        }

        $cashBank = CashBankAccount::findOrFail($data['cash_bank_account_id']);

        $payment = ArPayment::create([
            'ar_document_id'      => $ar->id,
            'payment_no'          => $this->docNumber->next('AR-PAY'), 
            'payment_date'        => $data['payment_date'],
            'amount'              => $amount,
            'cash_bank_account_id'=> $cashBank->id,
            'notes'               => $data['notes'] ?? null,
            'created_by'          => Auth::id(),
        ]);

        $newPaid    = $ar->paid_amount + $amount;
        $newBalance = $ar->original_amount - $newPaid;
        $ar->update([
            'paid_amount'    => $newPaid,
            'balance_amount' => $newBalance,
            'status'         => $newBalance <= 0 ? 'paid' : 'partial',
        ]);

        $journal = $this->journalService->postFromMapping(
            event: 'ar_payment',
            amount: $amount,
            date: $data['payment_date'],
            description: 'Penerimaan piutang ' . $ar->document_no . ' dari ' . $ar->customer->name,
            refType: ArPayment::class,
            refId: $payment->id,
            overrideCreditAccountId: null,
            overrideDebitAccountId: $cashBank->account_id,
        );

        $payment->update(['journal_entry_id' => $journal->id]);

        $this->cashBankService->record(
            account: $cashBank,
            direction: 'in',
            amount: $amount,
            description: 'Penerimaan piutang: ' . $ar->document_no,
            reference: $payment,
            journalEntry: $journal, // ◄── SEKARANG TIDAK NULL LAGI, MELAINKAN VARIABEL $journal
            transactionDate: $payment->payment_date
        );

        // Activity log
        activity_log(
            'ar_payment', 
            $payment->id, 
            'payment',
            'Pembayaran piutang sebesar Rp ' . number_format($amount, 0, ',', '.') . ' untuk dokumen ' . $ar->document_no // <── Menggunakan document_no milik ArDocument
        );

        return $payment;
    });
}
}