<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('document_no', 30)->unique();
                $table->date('entry_date');
                $table->string('description', 255);
                $table->enum('status', ['draft', 'posted', 'void'])->default('draft');
                $table->string('source_type', 50)->nullable(); // purchase, sale, dll
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('entry_type', 30)->default('manual'); // manual, auto
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['source_type', 'source_id'], 'je_source_idx');
                $table->index(['entry_date', 'status'], 'je_date_status_idx');
            });
        }

        if (!Schema::hasTable('journal_lines')) {
            Schema::create('journal_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
                $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
                $table->decimal('debit', 18, 2)->default(0);
                $table->decimal('credit', 18, 2)->default(0);
                $table->string('description', 255)->nullable();
                $table->timestamps();
                $table->index('account_id', 'jl_account_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
    }
};