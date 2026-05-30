<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('website_settings') || ! Schema::hasColumn('website_settings', 'google_map_url')) {
            return;
        }

        Schema::table('website_settings', function (Blueprint $table) {
            $table->text('google_map_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('website_settings') || ! Schema::hasColumn('website_settings', 'google_map_url')) {
            return;
        }

        Schema::table('website_settings', function (Blueprint $table) {
            $table->string('google_map_url')->nullable()->change();
        });
    }
};
