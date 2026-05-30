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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 100);
            $table->enum('type', ['pos', 'activity']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('pos_settings')->nullable();
            $table->json('activity_settings')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'slug']);
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
