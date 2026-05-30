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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->foreignId('cash_register_id')
                ->constrained('cash_registers')
                ->restrictOnDelete();

            $table->foreignId('received_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('method', [
                'cash',
                'mobile_money',
                'card',
                'bank_transfer',
                'wallet',
            ]);

            $table->decimal('amount', 12, 2);

            $table->string('transaction_reference')->nullable();

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
                'refunded',
            ])->default('completed');

            $table->text('notes')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index('sale_id');
            $table->index('branch_id');
            $table->index('module_id');
            $table->index('cash_register_id');
            $table->index('method');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
