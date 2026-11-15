<?php

namespace App\Services;

use App\Models\ApDocument;
use App\Models\ApPayment;
use App\Models\CashBankAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApPaymentService
{
    public function __construct(
        private JournalService   $journalService,
        private CashBankService  $cashBankService,
        private DocumentNumberService $docNumber,
    ) {}

    public function pay(ApDocument $ap, array $data): ApPayment
{
    return DB::transaction(function () use ($ap, $data) {
        $amount = (float) $data['amount'];

        if ($amount > $ap->balance_amount) {
            throw new \Exception('Jumlah pembayaran melebihi sisa hutang.');
        }

        $cashBank = CashBankAccount::findOrFail($data['cash_bank_account_id']);

        // 1. Buat payment record menggunakan payment_no
        $payment = ApPayment::create([
            'ap_document_id'       => $ap->id,
            'payment_no'           => $this->docNumber->next('AP-PAY'), // Pastikan menggunakan next() agar urut rapi
            'payment_date'         => $data['payment_date'],
            'amount'               => $amount,
            'cash_bank_account_id' => $cashBank->id,
            'notes'                => $data['notes'] ?? null,
            'created_by'           => Auth::id(),
        ]);

        // 2. Update saldo AP
        $newPaid    = $ap->paid_amount + $amount;
        $newBalance = $ap->original_amount - $newPaid;
        $ap->update([
            'paid_amount'    => $newPaid,
            'balance_amount' => $newBalance,
            'status'         => $newBalance <= 0 ? 'paid' : 'partial',
        ]);

        // 3. Jurnal: Debit Hutang Usaha, Kredit Kas (DINAIKKAN KE SINI)
        $journal = $this->journalService->postFromMapping(
            event: 'ap_payment',
            amount: $amount,
            date: $data['payment_date'],
            description: 'Pembayaran hutang ' . $ap->document_no . ' ke ' . $ap->supplier->name,
            refType: ApPayment::class,
            refId: $payment->id,
            overrideCreditAccountId: $cashBank->account_id,
            overrideDebitAccountId: null,
        );

        // Update journal_entry_id ke record payment
        $payment->update(['journal_entry_id' => $journal->id]);

        // 4. Mutasi kas keluar (SEKARANG $journal SUDAH VALID & ADA)
        $this->cashBankService->record(
            account: $cashBank,
            direction: 'out',
            amount: $amount,
            description: 'Pembayaran hutang: ' . $ap->document_no,
            reference: $payment,
            journalEntry: $journal, 
            transactionDate: $payment->payment_date
        );

        // 5. Activity log
        activity_log(
            'ap_payment', 
            $payment->id, 
            'payment',
            'Pembayaran hutang ' . $ap->document_no . ' Rp ' . number_format($amount, 0, ',', '.')
        );

        return $payment;
    });
}
}