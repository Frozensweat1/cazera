<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_purchase_order_requests')) {
            $this->ensureIndexes();
            return;
        }

        Schema::create('inventory_purchase_order_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference_no')->unique();
            $table->decimal('requested_qty', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->decimal('quantity_before', 12, 2)->nullable();
            $table->decimal('quantity_after', 12, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('approval_notes')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'module_id', 'status'], 'ipor_branch_module_status_idx');
            $table->index(['inventory_item_id', 'status'], 'ipor_item_status_idx');
            $table->index('supplier_id', 'ipor_supplier_idx');
            $table->index('requested_by', 'ipor_requested_by_idx');
            $table->index('approved_by', 'ipor_approved_by_idx');
        });
    }

    private function ensureIndexes(): void
    {
        Schema::table('inventory_purchase_order_requests', function (Blueprint $table) {
            if (! $this->indexExists('ipor_branch_module_status_idx')) {
                $table->index(['branch_id', 'module_id', 'status'], 'ipor_branch_module_status_idx');
            }

            if (! $this->indexExists('ipor_item_status_idx')) {
                $table->index(['inventory_item_id', 'status'], 'ipor_item_status_idx');
            }

            if (! $this->indexExists('ipor_supplier_idx')) {
                $table->index('supplier_id', 'ipor_supplier_idx');
            }

            if (! $this->indexExists('ipor_requested_by_idx')) {
                $table->index('requested_by', 'ipor_requested_by_idx');
            }

            if (! $this->indexExists('ipor_approved_by_idx')) {
                $table->index('approved_by', 'ipor_approved_by_idx');
            }
        });
    }

    private function indexExists(string $name): bool
    {
        return collect(DB::select('SHOW INDEX FROM inventory_purchase_order_requests'))
            ->contains(fn ($index) => ($index->Key_name ?? null) === $name);
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_purchase_order_requests');
    }
};
