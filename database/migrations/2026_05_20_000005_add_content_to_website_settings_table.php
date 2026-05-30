<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('website_settings')) {
            return;
        }

        Schema::table('website_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('website_settings', 'content')) {
                $table->json('content')->nullable()->after('meta_description');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('website_settings') || ! Schema::hasColumn('website_settings', 'content')) {
            return;
        }

        Schema::table('website_settings', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
