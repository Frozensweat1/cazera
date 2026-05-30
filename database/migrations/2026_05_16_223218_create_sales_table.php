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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('sale_number', 50);

            $table->enum('type', [
                'dine_in',
                'takeaway',
                'delivery',
                'online',
            ])->default('dine_in');

            $table->enum('status', [
                'pending',
                'confirmed',
                'cooking',
                'ready',
                'served',
                'completed',
                'cancelled',
                'refunded',
            ])->default('pending');

            $table->decimal('subtotal', 12, 2);

            $table->decimal('tax', 12, 2)->default(0);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('service_charge', 12, 2)->default(0);

            $table->decimal('total', 12, 2);

            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->decimal('remaining_balance', 12, 2)->default(0);

            $table->boolean('is_debt')->default(false);

            $table->timestamp('sale_date')->useCurrent();

            $table->timestamp('served_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['branch_id', 'sale_number']);

            $table->index('status');
            $table->index('type');
            $table->index('customer_id');
            $table->index('module_id');
            $table->index('created_by');
            $table->index('sale_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
