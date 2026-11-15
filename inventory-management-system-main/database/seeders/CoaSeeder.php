<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Support\AccurateAccountMapper;
use Illuminate\Database\Seeder;

class CoaSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/coa.tsv');

        if (! file_exists($path)) {
            $this->command?->error('COA data file not found.');

            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $codeToId = [];
        $sort = 0;

        foreach ($lines as $line) {
            [$code, $name, $accurateType, $openingBalance, $isHeader] = explode("\t", $line);
            $parentCode = $this->resolveParentCode($code);
            $parentId = $parentCode ? ($codeToId[$parentCode] ?? null) : null;
            [$accountType, $normalBalance] = AccurateAccountMapper::map($accurateType);
            $level = substr_count($code, '.') + (is_numeric(str_replace('.', '', $code)) && strlen(str_replace('.', '', $code)) <= 4 && ! str_contains($code, '.') ? 1 : 1);

            if (! str_contains($code, '.')) {
                $level = 1;
            } elseif (substr_count($code, '.') === 1) {
                $level = 2;
            } elseif (substr_count($code, '.') === 2) {
                $level = 3;
            } else {
                $level = 4;
            }

            $account = Account::create([
                'parent_id' => $parentId,
                'code' => $code,
                'name' => $name,
                'account_type' => $accountType->value,
                'normal_balance' => $normalBalance,
                'accurate_type' => $accurateType,
                'is_header' => (bool) $isHeader,
                'is_postable' => ! (bool) $isHeader,
                'is_active' => true,
                'level' => $level,
                'sort_order' => ++$sort,
                'opening_balance' => $openingBalance,
            ]);

            $codeToId[$code] = $account->id;
        }
    }

    private function resolveParentCode(string $code): ?string
    {
        if (! str_contains($code, '.')) {
            return null;
        }

        $parts = explode('.', $code);
        array_pop($parts);

        return implode('.', $parts);
    }
}
