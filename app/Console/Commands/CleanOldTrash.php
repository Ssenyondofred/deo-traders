<?php

namespace App\Console\Commands;

use App\Models\Sale;
use Illuminate\Console\Command;

class CleanOldTrash extends Command
{
    protected $signature = 'trash:clean';
    protected $description = 'Permanently delete sales older than 90 days from trash';

    public function handle()
    {
        $count = Sale::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(90))
            ->forceDelete();

        $this->info("Permanently deleted {$count} old invoices.");
        return 0;
    }
}