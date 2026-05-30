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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->boolean('is_kitchen_notified')->default(false)->after('status');
            $table->enum('kitchen_status', ['pending', 'queued', 'cooking', 'ready', 'completed'])
                ->default('pending')
                ->after('is_kitchen_notified');
            $table->timestamp('kitchen_started_at')->nullable()->after('kitchen_status');
            $table->timestamp('kitchen_completed_at')->nullable()->after('kitchen_started_at');

            $table->index('is_kitchen_notified');
            $table->index('kitchen_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['is_kitchen_notified']);
            $table->dropIndex(['kitchen_status']);
            $table->dropColumn([
                'is_kitchen_notified',
                'kitchen_status',
                'kitchen_started_at',
                'kitchen_completed_at',
            ]);
        });
    }
};
