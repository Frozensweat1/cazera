<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cash_register_id')
                ->constrained('cash_registers')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->nullOnDelete();

            $table->foreignId('sale_id')
                ->nullable()
                ->constrained('sales')
                ->nullOnDelete();

            $table->foreignId('performed_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('type', [
                'sale',
                'refund',
                'expense',
                'cash_in',
                'cash_out',
                'opening_balance',
                'closing_balance',
            ]);

            $table->decimal('amount', 12, 2);

            $table->text('notes')->nullable();

            $table->timestamp('transaction_date')->useCurrent();

            $table->timestamps();

            $table->index('cash_register_id');
            $table->index('branch_id');
            $table->index('module_id');
            $table->index('sale_id');
            $table->index('performed_by');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_transactions');
    }
};
