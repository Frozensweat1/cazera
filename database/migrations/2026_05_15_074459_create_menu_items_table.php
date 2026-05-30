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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->integer('quantity')->nullable()->default(0);
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->integer('preparation_time')->default(0);
            $table->enum('status', ['available', 'unavailable', 'out_of_stock'])->default('available');
            $table->boolean('is_trackable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['branch_id', 'module_id', 'category_id', 'slug'], 'menu_items_branch_module_category_slug_unique');
            $table->index('module_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('is_trackable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
