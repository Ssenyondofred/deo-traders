<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function next(string $prefix): string
    {
        $year = (int) now()->format('Y');

        return DB::transaction(function () use ($prefix, $year) {
            $sequence = DocumentSequence::lockForUpdate()
                ->firstOrCreate(
                    ['prefix' => $prefix, 'year' => $year],
                    ['last_number' => 0]
                );

            $sequence->increment('last_number');
            $number = str_pad((string) $sequence->last_number, 4, '0', STR_PAD_LEFT);

            return sprintf('%s-%d-%s', $prefix, $year, $number);
        });
    }

    public function generate(string $prefix): string
    {
        // Contoh logika generator sederhana: PREFIX-TahunBulan-AngkaAcak/Sequence
        // Misal: AR-PAY-202605-1024
        $date = now()->format('Ymd');
        $random = rand(1000, 9999); 
        
        return "{$prefix}-{$date}-{$random}";
    }
}
