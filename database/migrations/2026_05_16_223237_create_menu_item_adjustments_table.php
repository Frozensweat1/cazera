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
        Schema::create('menu_item_adjustments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->foreignId('menu_item_id')
                ->constrained('menu_items')
                ->cascadeOnDelete();

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
                'purchase',
                'adjustment_increase',
                'adjustment_decrease',
                'manual_set',
                'transfer_in',
                'transfer_out',
                'wastage',
                'production',
                'opening_stock',
            ]);

            $table->decimal('quantity_before', 12, 2);

            $table->decimal('quantity_after', 12, 2);

            $table->decimal('change_qty', 12, 2);

            $table->string('reference_no')->nullable();

            $table->string('reason')->nullable();

            $table->text('notes')->nullable();

            $table->timestamp('transaction_date')->useCurrent();

            $table->timestamps();

            $table->index(['branch_id', 'menu_item_id']);
            $table->index('module_id');
            $table->index('sale_id');
            $table->index('type');
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_adjustments');
    }
};
