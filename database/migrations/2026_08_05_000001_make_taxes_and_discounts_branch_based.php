<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deduplicateByBranch('taxes', 'name');
        $this->deduplicateByBranch('discounts', 'name');
        $this->deduplicateByBranch('discounts', 'code', ignoreBlank: true);

        if (Schema::hasTable('taxes')) {
            Schema::table('taxes', function (Blueprint $table) {
                $this->addIndexIfMissing($table, 'taxes', ['branch_id', 'is_active'], 'taxes_branch_id_is_active_index');
            });

            $this->dropForeignIfExists('taxes', 'taxes_module_id_foreign');

            Schema::table('taxes', function (Blueprint $table) {
                $this->dropIndexIfColumnExists($table, 'taxes', 'module_id', 'taxes_branch_id_module_id_name_unique', 'unique');
                $this->dropIndexIfColumnExists($table, 'taxes', 'module_id', 'taxes_branch_id_module_id_is_active_index');

                if (Schema::hasColumn('taxes', 'module_id')) {
                    $table->dropColumn('module_id');
                }
            });

            Schema::table('taxes', function (Blueprint $table) {
                $this->addUniqueIfMissing($table, 'taxes', ['branch_id', 'name'], 'taxes_branch_id_name_unique');
            });
        }

        if (Schema::hasTable('discounts')) {
            Schema::table('discounts', function (Blueprint $table) {
                $this->addIndexIfMissing($table, 'discounts', ['branch_id', 'is_active'], 'discounts_branch_id_is_active_index');
            });

            $this->dropForeignIfExists('discounts', 'discounts_module_id_foreign');

            Schema::table('discounts', function (Blueprint $table) {
                $this->dropIndexIfColumnExists($table, 'discounts', 'module_id', 'discounts_branch_id_module_id_name_unique', 'unique');
                $this->dropIndexIfColumnExists($table, 'discounts', 'module_id', 'discounts_branch_id_module_id_code_unique', 'unique');
                $this->dropIndexIfColumnExists($table, 'discounts', 'module_id', 'discounts_branch_id_module_id_is_active_index');

                if (Schema::hasColumn('discounts', 'module_id')) {
                    $table->dropColumn('module_id');
                }
            });

            Schema::table('discounts', function (Blueprint $table) {
                $this->addUniqueIfMissing($table, 'discounts', ['branch_id', 'name'], 'discounts_branch_id_name_unique');
                $this->addUniqueIfMissing($table, 'discounts', ['branch_id', 'code'], 'discounts_branch_id_code_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('taxes') && ! Schema::hasColumn('taxes', 'module_id')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->dropUnique(['branch_id', 'name']);
                $table->dropIndex(['branch_id', 'is_active']);
                $table->foreignId('module_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
                $table->unique(['branch_id', 'module_id', 'name']);
                $table->index(['branch_id', 'module_id', 'is_active']);
            });
        }

        if (Schema::hasTable('discounts') && ! Schema::hasColumn('discounts', 'module_id')) {
            Schema::table('discounts', function (Blueprint $table) {
                $table->dropUnique(['branch_id', 'name']);
                $table->dropUnique(['branch_id', 'code']);
                $table->dropIndex(['branch_id', 'is_active']);
                $table->foreignId('module_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
                $table->unique(['branch_id', 'module_id', 'name']);
                $table->unique(['branch_id', 'module_id', 'code']);
                $table->index(['branch_id', 'module_id', 'is_active']);
            });
        }
    }

    private function deduplicateByBranch(string $table, string $column, bool $ignoreBlank = false): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $duplicates = DB::table($table)
            ->select('branch_id', $column)
            ->when($ignoreBlank, fn ($query) => $query->whereNotNull($column)->where($column, '!=', ''))
            ->groupBy('branch_id', $column)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $value = $duplicate->{$column};

            DB::table($table)
                ->where('branch_id', $duplicate->branch_id)
                ->where($column, $value)
                ->orderBy('id')
                ->pluck('id')
                ->skip(1)
                ->each(function ($id) use ($table, $column, $value) {
                    DB::table($table)
                        ->where('id', $id)
                        ->update([$column => $value . ' #' . $id]);
                });
        }
    }

    private function dropIndexIfColumnExists(Blueprint $table, string $tableName, string $column, string $index, string $type = 'index'): void
    {
        if (! Schema::hasColumn($tableName, $column) || ! Schema::hasIndex($tableName, $index)) {
            return;
        }

        if ($type === 'unique') {
            $table->dropUnique($index);
            return;
        }

        $table->dropIndex($index);
    }

    private function addIndexIfMissing(Blueprint $table, string $tableName, array $columns, string $index): void
    {
        if (Schema::hasIndex($tableName, $index)) {
            return;
        }

        $table->index($columns, $index);
    }

    private function addUniqueIfMissing(Blueprint $table, string $tableName, array $columns, string $index): void
    {
        if (Schema::hasIndex($tableName, $index)) {
            return;
        }

        $table->unique($columns, $index);
    }

    private function dropForeignIfExists(string $table, string $foreignKey): void
    {
        $exists = DB::table('information_schema.table_constraints')
            ->whereRaw('constraint_schema = database()')
            ->where('table_name', $table)
            ->where('constraint_name', $foreignKey)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();

        if (! $exists) {
            return;
        }

        DB::statement("alter table `{$table}` drop foreign key `{$foreignKey}`");
    }
};
