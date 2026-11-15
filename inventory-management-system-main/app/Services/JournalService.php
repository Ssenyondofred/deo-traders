<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountMapping;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalService
{
    /**
     * Buat jurnal manual (dari controller General Journal)
     */
    public function createManual(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $entry = JournalEntry::create([
                'entry_no'    => app(DocumentNumberService::class)->generate('JE'),
                'entry_date'  => $data['entry_date'],
                'description' => $data['description'],
                'entry_type'  => 'manual',
                'status'      => 'draft',
                'created_by'  => Auth::id(),
            ]);

            foreach ($data['lines'] as $line) {
                if (empty($line['account_id'])) continue;
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'debit'            => (float)($line['debit'] ?? 0),
                    'credit'           => (float)($line['credit'] ?? 0),
                    'description'      => $line['description'] ?? null,
                ]);
            }

            if (!$entry->load('lines')->isBalanced()) {
                throw new \Exception('Jurnal tidak balance (jumlah Debit dan Kredit harus sama).');
            }

            return $entry;
        });
    }

    /**
     * Post jurnal (draft → posted) — hanya admin
     */
    public function post(JournalEntry $entry): JournalEntry
    {
        if ($entry->status !== 'draft') {
            throw new \Exception('Hanya jurnal berstatus Draft yang bisa di-post.');
        }

        if (!$entry->load('lines')->isBalanced()) {
            throw new \Exception('Jurnal tidak balance, tidak bisa di-post.');
        }

        $entry->update([
            'status'    => 'posted',
            'posted_by' => Auth::id(),
            'posted_at' => now(),
        ]);

        activity_log('journal_entry', $entry->id, 'posted', 'Post jurnal '.$entry->entry_no);

        return $entry;
    }

    /**
     * Void jurnal (posted → void) dengan jurnal pembalik
     */
    public function void(JournalEntry $entry, string $reason = ''): JournalEntry
    {
        if ($entry->status !== 'posted') {
            throw new \Exception('Hanya jurnal Posted yang bisa di-void.');
        }

        return DB::transaction(function () use ($entry, $reason) {
            // Buat jurnal pembalik
            $reversal = JournalEntry::create([
                'entry_no'       => app(DocumentNumberService::class)->generate('JE'),
                'entry_date'     => now()->toDateString(),
                'description'    => 'VOID: '.$entry->entry_no.' — '.$reason,
                'entry_type'     => 'reversal',
                'status'         => 'posted',
                'reference_type' => JournalEntry::class,
                'reference_id'   => $entry->id,
                'created_by'     => Auth::id(),
                'posted_by'      => Auth::id(),
                'posted_at'      => now(),
            ]);

            foreach ($entry->lines as $line) {
                JournalLine::create([
                    'journal_entry_id' => $reversal->id,
                    'account_id'       => $line->account_id,
                    'debit'            => $line->credit, // dibalik
                    'credit'           => $line->debit,
                    'description'      => 'Pembalik: '.$line->description,
                ]);
            }

            $entry->update(['status' => 'void']);

            activity_log('journal_entry', $entry->id, 'void', 'Void jurnal '.$entry->entry_no.'. Alasan: '.$reason);

            return $reversal;
        });
    }

    /**
     * Post jurnal otomatis dari modul (purchase, sale, dll)
     * Dipanggil oleh PurchaseService, SaleService, dll.
     */
    public function postFromMapping(
        string $event,
        float $amount,
        string|\DateTimeInterface $date,
        string $description,
        string $refType,
        int $refId,
        ?int $overrideDebitAccountId = null,
        ?int $overrideCreditAccountId = null,
    ): JournalEntry {
        return DB::transaction(function () use (
            $event, $amount, $date, $description,
            $refType, $refId, $overrideDebitAccountId, $overrideCreditAccountId
        ) {
            $mapping = AccountMapping::where('event_code', $event)->first();

            if (!$mapping) {
                throw new \Exception("Account mapping untuk event '{$event}' tidak ditemukan.");
            }

            $debitAccountId  = $overrideDebitAccountId  ?? $mapping->debit_account_id;
            $creditAccountId = $overrideCreditAccountId ?? $mapping->credit_account_id;

            // KODE SUDAH DISESUAIKAN DENGAN STRUKTUR DATABASE ASLI KAMU
            $entry = JournalEntry::create([
                'entry_no'       => app(DocumentNumberService::class)->generate('JE'),
                'entry_date'     => is_string($date) ? $date : $date->format('Y-m-d'),
                'description'    => $description,
                'entry_type'     => 'auto',
                'status'         => 'posted',
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'created_by'     => Auth::id(),
                'posted_by'      => Auth::id(),
                'posted_at'      => now(),
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $debitAccountId,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => $description,
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $creditAccountId,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => $description,
            ]);

            return $entry;
        });
    }

    public function accountIdByCode(string $code): ?int
    {   // Mencari akun di database berdasarkan kolom 'code'
        $account = Account::where('code', $code)->first();

        if (!$account) {
            throw new \Exception("Akun dengan kode '{$code}' tidak ditemukan di pengaturan COA.");
        }

        return $account->id;
    }
}