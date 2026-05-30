<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('notes');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->foreignId('locked_by')
                ->nullable()
                ->after('locked_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('is_locked');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropIndex(['is_locked']);
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by']);
        });
    }
};
