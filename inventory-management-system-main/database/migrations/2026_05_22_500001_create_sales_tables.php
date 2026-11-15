<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_bom_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('component_product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('qty_per_unit', 18, 6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['finished_product_id', 'component_product_id'], 'sale_bom_unique');
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_no', 40)->unique();
            $table->date('sale_date');
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('payment_method', 20);
            $table->foreignId('cash_bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('draft');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->default(0);
            $table->boolean('is_taxable')->default(false);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->decimal('total_cogs', 18, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'sale_date']);
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('qty', 18, 4);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('line_total', 18, 2);
            $table->boolean('is_main_product')->default(false);
            $table->boolean('is_bom_component')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ar_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_no', 40)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->decimal('original_amount', 18, 2);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->decimal('balance_amount', 18, 2);
            $table->string('status', 20)->default('open');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['customer_id', 'status']);
        });

        Schema::create('ar_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 40)->unique();
            $table->foreignId('ar_document_id')->constrained()->restrictOnDelete();
            $table->foreignId('cash_bank_account_id')->constrained()->restrictOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 18, 2);
            $table->string('notes')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_payments');
        Schema::dropIfExists('ar_documents');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('sale_bom_rules');
    }
};
