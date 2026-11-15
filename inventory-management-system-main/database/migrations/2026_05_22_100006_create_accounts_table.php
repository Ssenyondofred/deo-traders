<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('account_type', 40);
            $table->string('normal_balance', 10);
            $table->string('accurate_type', 80)->nullable();
            $table->boolean('is_header')->default(false);
            $table->boolean('is_postable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->timestamps();

            $table->index(['account_type', 'is_active']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
