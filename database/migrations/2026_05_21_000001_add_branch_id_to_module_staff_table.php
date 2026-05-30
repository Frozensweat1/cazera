<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('module_staff')) {
            return;
        }

        Schema::table('module_staff', function (Blueprint $table) {
            if (! Schema::hasColumn('module_staff', 'branch_id')) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('branches')
                    ->cascadeOnDelete();
            }
        });

        DB::table('module_staff')
            ->join('modules', 'module_staff.module_id', '=', 'modules.id')
            ->whereNull('module_staff.branch_id')
            ->update(['module_staff.branch_id' => DB::raw('modules.branch_id')]);

        if (Schema::hasColumn('module_staff', 'branch_id')) {
            Schema::table('module_staff', function (Blueprint $table) {
                $table->index(['user_id', 'branch_id'], 'module_staff_user_branch_index');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('module_staff') || ! Schema::hasColumn('module_staff', 'branch_id')) {
            return;
        }

        Schema::table('module_staff', function (Blueprint $table) {
            $table->dropIndex('module_staff_user_branch_index');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
