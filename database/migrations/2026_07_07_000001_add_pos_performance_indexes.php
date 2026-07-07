<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('menu_items', ['branch_id', 'module_id', 'status', 'name'], 'menu_items_pos_lookup_idx');
        $this->addIndexIfMissing('sales', ['branch_id', 'status', 'sale_date'], 'sales_pos_today_idx');
        $this->addIndexIfMissing('sale_items', ['module_id', 'sale_id'], 'sale_items_module_sale_idx');
        $this->addIndexIfMissing('tables', ['branch_id', 'status'], 'tables_branch_status_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('menu_items', 'menu_items_pos_lookup_idx');
        $this->dropIndexIfExists('sales', 'sales_pos_today_idx');
        $this->dropIndexIfExists('sale_items', 'sale_items_module_sale_idx');
        $this->dropIndexIfExists('tables', 'tables_branch_status_idx');
    }

    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || Schema::hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropIndex($name);
        });
    }
};
