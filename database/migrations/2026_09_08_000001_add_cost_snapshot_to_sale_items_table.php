<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 2)->default(0)->after('unit_price');
            $table->boolean('is_trackable')->default(false)->after('unit_cost');
        });

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement(<<<'SQL'
                UPDATE sale_items
                INNER JOIN menu_items ON menu_items.id = sale_items.menu_item_id
                SET sale_items.unit_cost = COALESCE(menu_items.cost_price, 0),
                    sale_items.is_trackable = menu_items.is_trackable
            SQL),
            'pgsql' => DB::statement(<<<'SQL'
                UPDATE sale_items
                SET unit_cost = COALESCE(menu_items.cost_price, 0),
                    is_trackable = menu_items.is_trackable
                FROM menu_items
                WHERE menu_items.id = sale_items.menu_item_id
            SQL),
            default => DB::statement(<<<'SQL'
                UPDATE sale_items
                SET unit_cost = COALESCE((
                        SELECT menu_items.cost_price
                        FROM menu_items
                        WHERE menu_items.id = sale_items.menu_item_id
                    ), 0),
                    is_trackable = COALESCE((
                        SELECT menu_items.is_trackable
                        FROM menu_items
                        WHERE menu_items.id = sale_items.menu_item_id
                    ), 0)
            SQL),
        };
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'is_trackable']);
        });
    }
};
