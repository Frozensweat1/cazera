<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->indexIfMissing('testimonials', ['branch_id', 'is_published', 'is_featured'], 'testimonials_branch_publish_feature_index');
        $this->indexIfMissing('reviews', ['branch_id', 'is_approved', 'approved_at'], 'reviews_branch_approved_at_index');
        $this->indexIfMissing('reviews', ['menu_item_id', 'is_approved'], 'reviews_menu_item_approved_index');
        $this->indexIfMissing('contact_messages', ['status', 'received_at'], 'contact_messages_status_received_index');
        $this->indexIfMissing('contact_messages', ['branch_id', 'status'], 'contact_messages_branch_status_index');
        $this->indexIfMissing('website_pages', ['is_published', 'sort_order'], 'website_pages_publish_sort_index');
        $this->indexIfMissing('gallery_items', ['branch_id', 'is_published', 'sort_order'], 'gallery_items_branch_publish_sort_index');
        $this->indexIfMissing('website_events', ['branch_id', 'is_published', 'starts_at'], 'website_events_branch_publish_starts_index');
    }

    public function down(): void
    {
        $this->dropIndexIfTableExists('website_events', 'website_events_branch_publish_starts_index');
        $this->dropIndexIfTableExists('gallery_items', 'gallery_items_branch_publish_sort_index');
        $this->dropIndexIfTableExists('website_pages', 'website_pages_publish_sort_index');
        $this->dropIndexIfTableExists('contact_messages', 'contact_messages_branch_status_index');
        $this->dropIndexIfTableExists('contact_messages', 'contact_messages_status_received_index');
        $this->dropIndexIfTableExists('reviews', 'reviews_menu_item_approved_index');
        $this->dropIndexIfTableExists('reviews', 'reviews_branch_approved_at_index');
        $this->dropIndexIfTableExists('testimonials', 'testimonials_branch_publish_feature_index');
    }

    private function indexIfMissing(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || Schema::hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndexIfTableExists(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropIndex($name);
        });
    }
};
