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
        Schema::create('sale_items', function (Blueprint $table) {
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

            $table->foreignId('menu_item_id')
                ->constrained('menu_items')
                ->restrictOnDelete();

            $table->string('item_name');

            $table->string('sku')->nullable();

            $table->decimal('qty', 12, 2)->default(1);

            $table->decimal('unit_price', 12, 2)->default(0);

            $table->decimal('tax', 12, 2)->default(0);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('subtotal', 12, 2)->default(0);

            $table->decimal('total', 12, 2)->default(0);

            $table->enum('status', [
                'pending',
                'preparing',
                'ready',
                'served',
                'cancelled',
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamp('prepared_at')->nullable();

            $table->timestamp('served_at')->nullable();

            $table->timestamps();

            $table->index('sale_id');
            $table->index('branch_id');
            $table->index('module_id');
            $table->index('menu_item_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
