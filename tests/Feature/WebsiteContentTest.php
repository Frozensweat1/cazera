<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\GalleryItem;
use App\Models\WebsiteSetting;
use App\Support\WebsiteContent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebsiteContentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('location')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->nullable();
            $table->string('tagline')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->text('google_map_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('x_url')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('hero_background')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('content')->nullable();
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('category');
            $table->string('type')->default('image');
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        WebsiteContent::flushMemoized();
    }

    public function test_real_branches_do_not_inherit_demo_contact_or_location_content(): void
    {
        Branch::create([
            'name' => 'Cazera Tamale',
            'slug' => 'tamale',
            'location' => 'Gumani, Tamale',
            'is_active' => true,
        ]);

        $branch = WebsiteContent::branch('tamale');

        $this->assertSame('Gumani, Tamale', $branch['location']);
        $this->assertNull($branch['phone']);
        $this->assertStringContainsString('Cazera Tamale', $branch['description']);
        $this->assertStringNotContainsString('Lagos', $branch['description']);
    }

    public function test_empty_published_collections_do_not_return_demo_records(): void
    {
        $this->assertTrue(WebsiteContent::branches()->isEmpty());
        $this->assertTrue(WebsiteContent::gallery()->isEmpty());
    }

    public function test_gallery_items_are_scoped_to_the_requested_branch(): void
    {
        $firstBranch = Branch::create(['name' => 'First', 'slug' => 'first', 'is_active' => true]);
        $secondBranch = Branch::create(['name' => 'Second', 'slug' => 'second', 'is_active' => true]);

        GalleryItem::create([
            'branch_id' => $firstBranch->id,
            'title' => 'First room',
            'slug' => 'first-room',
            'category' => 'interiors',
            'type' => 'image',
            'image' => 'first.jpg',
            'is_published' => true,
        ]);
        GalleryItem::create([
            'branch_id' => $secondBranch->id,
            'title' => 'Second room',
            'slug' => 'second-room',
            'category' => 'interiors',
            'type' => 'image',
            'image' => 'second.jpg',
            'is_published' => true,
        ]);

        $items = WebsiteContent::gallery($firstBranch->id);

        $this->assertCount(1, $items);
        $this->assertSame('First room', $items->first()['title']);
    }

    public function test_settings_are_loaded_once_per_request(): void
    {
        WebsiteSetting::create(['business_name' => 'Cazera']);
        WebsiteContent::flushMemoized();
        $settingsQueries = 0;

        DB::listen(function ($query) use (&$settingsQueries): void {
            if (preg_match('/from [`\"]website_settings[`\"]/i', $query->sql) === 1) {
                $settingsQueries++;
            }
        });

        WebsiteContent::settings();
        WebsiteContent::copy('brand.name');
        WebsiteContent::settings();

        $this->assertSame(1, $settingsQueries);
    }
}
