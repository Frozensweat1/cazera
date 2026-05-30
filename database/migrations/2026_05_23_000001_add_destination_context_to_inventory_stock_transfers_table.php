<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_stock_transfers', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_stock_transfers', 'destination_module_id')) {
                $table->foreignId('destination_module_id')
                    ->nullable()
                    ->after('to_branch_id')
                    ->constrained('modules')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('inventory_stock_transfers', 'destination_category_id')) {
                $table->foreignId('destination_category_id')
                    ->nullable()
                    ->after('destination_module_id')
                    ->constrained('inventory_categories')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('inventory_stock_transfers', 'destination_supplier_id')) {
                $table->foreignId('destination_supplier_id')
                    ->nullable()
                    ->after('destination_category_id')
                    ->constrained('suppliers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_stock_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_stock_transfers', 'destination_supplier_id')) {
                $table->dropConstrainedForeignId('destination_supplier_id');
            }

            if (Schema::hasColumn('inventory_stock_transfers', 'destination_category_id')) {
                $table->dropConstrainedForeignId('destination_category_id');
            }

            if (Schema::hasColumn('inventory_stock_transfers', 'destination_module_id')) {
                $table->dropConstrainedForeignId('destination_module_id');
            }
        });
    }
};
