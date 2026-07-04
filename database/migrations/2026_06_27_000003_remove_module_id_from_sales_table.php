<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales', 'module_id')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('module_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'module_id')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('module_id')
                ->nullable()
                ->after('branch_id')
                ->constrained('modules')
                ->nullOnDelete();
        });
    }
};
