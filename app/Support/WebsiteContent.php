<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\CareerOpening;
use App\Models\Category;
use App\Models\GalleryItem;
use App\Models\MenuItem;
use App\Models\Review;
use App\Models\Testimonial;
use App\Models\WebsiteEvent;
use App\Models\WebsitePage;
use App\Models\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WebsiteContent
{
    public static function settings(): ?WebsiteSetting
    {
        if (! Schema::hasTable('website_settings')) {
            return null;
        }

        return WebsiteSetting::current();
    }

    public static function settingContent(string $key, mixed $default = null): mixed
    {
        return self::settings()?->contentValue($key, $default) ?? $default;
    }

    public static function page(string $slug, array $fallback = []): array
    {
        $page = Schema::hasTable('website_pages')
            ? WebsitePage::query()
                ->where('slug', $slug)
                ->where('is_published', true)
                ->first()
            : null;

        if (! $page) {
            return $fallback;
        }

        return array_filter([
            'slug' => $page->slug,
            'eyebrow' => $page->eyebrow,
            'title' => $page->title,
            'subtitle' => $page->subtitle,
            'body' => $page->body,
            'hero_image' => self::assetPath($page->hero_image),
            'sections' => $page->sections ?? [],
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public static function copy(string $key, mixed $default = null): mixed
    {
        return self::settingContent($key, $default);
    }

    public static function branches(): Collection
    {
        $fallbacks = collect(self::fallbackBranches());

        if (! Schema::hasTable('branches')) {
            return $fallbacks;
        }

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Branch $branch, int $index) => self::presentBranch($branch, $fallbacks[$index % $fallbacks->count()]));

        return $branches->isNotEmpty() ? $branches : $fallbacks;
    }

    public static function branch(string $slug): ?array
    {
        $fallback = collect(self::fallbackBranches())->firstWhere('slug', $slug);

        $branch = Schema::hasTable('branches')
            ? Branch::query()->where('slug', $slug)->where('is_active', true)->first()
            : null;

        if ($branch) {
            return self::presentBranch($branch, $fallback ?? self::fallbackBranches()[0]);
        }

        return $fallback;
    }

    public static function categories(?int $branchId = null): Collection
    {
        if (! Schema::hasTable('categories')) {
            return collect(self::fallbackCategories());
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'slug' => $category->slug ?: Str::slug($category->name),
                'name' => $category->name,
                'description' => $category->description ?: 'A guest-ready selection crafted for memorable visits and easy discovery.',
                'image' => self::assetPath($category->image_url) ?: self::menuFallbackAvatar($category->name),
            ]);

        return $categories->isNotEmpty() ? $categories : collect(self::fallbackCategories());
    }

    public static function menuItems(?int $branchId = null): Collection
    {
        if (! Schema::hasTable('menu_items')) {
            return collect(self::fallbackMenuItems());
        }

        $items = MenuItem::query()
            ->with(['category', 'branch', 'module'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when(! $branchId, fn ($query) => $query->limit(9))
            ->whereIn('status', ['active', 'available', 'published'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (MenuItem $item) => self::presentMenuItem($item));

        return $items->isNotEmpty() ? $items : collect(self::fallbackMenuItems());
    }

    public static function menuItem(string $slug): ?array
    {
        $fallback = collect(self::fallbackMenuItems())->firstWhere('slug', $slug);

        if (! Schema::hasTable('menu_items')) {
            return $fallback;
        }

        $item = MenuItem::query()
            ->with(['category', 'branch', 'module'])
            ->where('slug', $slug)
            ->whereIn('status', ['active', 'available', 'published'])
            ->first();

        return $item ? self::presentMenuItem($item) : $fallback;
    }

    public static function randomMenuItems(int $limit = 8, ?string $excludeSlug = null): Collection
    {
        if (! Schema::hasTable('menu_items')) {
            return collect(self::fallbackMenuItems())
                ->when($excludeSlug, fn ($items) => $items->where('slug', '!=', $excludeSlug))
                ->shuffle()
                ->take($limit)
                ->values();
        }

        $items = MenuItem::query()
            ->with(['category', 'branch', 'module'])
            ->whereIn('status', ['active', 'available', 'published'])
            ->when($excludeSlug, fn ($query) => $query->where('slug', '!=', $excludeSlug))
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(fn (MenuItem $item) => self::presentMenuItem($item));

        return $items->isNotEmpty() ? $items : collect(self::fallbackMenuItems())->shuffle()->take($limit)->values();
    }

    public static function testimonials(?int $branchId = null): Collection
    {
        if (! Schema::hasTable('testimonials')) {
            return collect(self::fallbackTestimonials());
        }

        $items = Testimonial::query()
            ->published()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(8)
            ->get()
            ->map(fn (Testimonial $testimonial) => [
                'name' => $testimonial->author_name,
                'title' => $testimonial->title ?: $testimonial->company,
                'quote' => $testimonial->quote,
                'rating' => $testimonial->rating ?: 5,
            ]);

        return $items->isNotEmpty() ? $items : collect(self::fallbackTestimonials());
    }

    public static function reviews(?int $branchId = null): Collection
    {
        if (! Schema::hasTable('reviews')) {
            return collect();
        }

        return Review::query()
            ->with(['branch', 'menuItem'])
            ->approved()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->latest('approved_at')
            ->limit(12)
            ->get();
    }

    public static function gallery(): Collection
    {
        $fallback = collect([
            ['category' => 'ambiance', 'title' => 'Golden hour arrival', 'type' => 'image', 'image' => self::image('photo-1517248135467-4c7edcad34c4', 1200)],
            ['category' => 'food', 'title' => 'Chef signatures', 'type' => 'image', 'image' => self::image('photo-1543353071-873f17a7a088', 1200)],
            ['category' => 'events', 'title' => 'Private celebrations', 'type' => 'image', 'image' => self::image('photo-1511795409834-ef04bbd61622', 1200)],
            ['category' => 'nightlife', 'title' => 'After dark lounge', 'type' => 'image', 'image' => self::image('photo-1572116469696-31de0f17cc34', 1200)],
            ['category' => 'vip', 'title' => 'Reserved experiences', 'type' => 'image', 'image' => self::image('photo-1566073771259-6a8506099945', 1200)],
            ['category' => 'interiors', 'title' => 'Textured interiors', 'type' => 'image', 'image' => self::image('photo-1552566626-52f8b828add9', 1200)],
            ['category' => 'food', 'title' => 'Plated with precision', 'type' => 'image', 'image' => self::image('photo-1559339352-11d035aa65de', 1200)],
            ['category' => 'ambiance', 'title' => 'Evening tables', 'type' => 'image', 'image' => self::image('photo-1414235077428-338989a2e8c0', 1200)],
            ['category' => 'events', 'title' => 'Live music night', 'type' => 'video', 'image' => self::image('photo-1505236858219-8359eb29e329', 1200)],
        ]);

        if (! Schema::hasTable('gallery_items')) {
            return $fallback;
        }

        $items = GalleryItem::query()
            ->published()
            ->with('branch')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (GalleryItem $item) => [
                'id' => $item->id,
                'category' => $item->category,
                'title' => $item->title,
                'type' => $item->type,
                'description' => $item->description,
                'image' => self::assetPath($item->image) ?: self::image('photo-1517248135467-4c7edcad34c4', 1200),
                'video_url' => $item->video_url,
            ]);

        return $items->isNotEmpty() ? $items : $fallback;
    }

    public static function events(): Collection
    {
        $fallback = collect([
            ['slug' => 'friday-live-sessions', 'title' => 'Friday Live Sessions', 'date' => 'Every Friday', 'tag' => 'Live music', 'description' => 'A polished night of soulful performances, chef specials and late conversations.', 'image' => self::image('photo-1492684223066-81342ee5ff30', 1200)],
            ['slug' => 'golden-hour-specials', 'title' => 'Golden Hour Specials', 'date' => 'Weekdays, 5-7 PM', 'tag' => 'Happy hour', 'description' => 'Signature drinks and small plates designed for an elegant pre-evening pause.', 'image' => self::image('photo-1513558161293-cdaf765ed2fd', 1200)],
            ['slug' => 'seasonal-tasting-week', 'title' => 'Seasonal Tasting Week', 'date' => 'This season', 'tag' => 'Campaign', 'description' => 'A curated tasting journey across house favorites, new plates and celebratory pours.', 'image' => self::image('photo-1551218808-94e220e084d2', 1200)],
        ]);

        if (! Schema::hasTable('website_events')) {
            return $fallback;
        }

        $events = WebsiteEvent::query()
            ->published()
            ->with('branch')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now()->startOfDay());
            })
            ->orderByDesc('is_featured')
            ->orderByRaw('starts_at IS NULL, starts_at ASC')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (WebsiteEvent $event) => [
                'id' => $event->id,
                'slug' => $event->slug,
                'title' => $event->title,
                'date' => $event->date_label ?: $event->starts_at?->format('M j, Y g:i A'),
                'tag' => $event->tag ?: 'Event',
                'description' => $event->description ?: Str::limit(strip_tags($event->body ?? ''), 180),
                'body' => $event->body,
                'image' => self::assetPath($event->image) ?: self::image('photo-1492684223066-81342ee5ff30', 1200),
            ]);

        return $events->isNotEmpty() ? $events : $fallback;
    }

    public static function careers(): Collection
    {
        $fallback = collect([
            ['role' => 'Guest Experience Host', 'location' => 'All branches', 'type' => 'Full-time'],
            ['role' => 'Chef de Partie', 'location' => 'Main lounge', 'type' => 'Full-time'],
            ['role' => 'Bar & Mixology Associate', 'location' => 'Nightlife module', 'type' => 'Shift-based'],
        ]);

        if (! Schema::hasTable('career_openings')) {
            return $fallback;
        }

        $openings = CareerOpening::query()
            ->active()
            ->with('branch')
            ->orderBy('sort_order')
            ->orderBy('role')
            ->get()
            ->map(fn (CareerOpening $opening) => [
                'id' => $opening->id,
                'role' => $opening->role,
                'slug' => $opening->slug,
                'location' => $opening->location ?: $opening->branch?->name ?: 'All branches',
                'type' => $opening->employment_type ?: 'Full-time',
                'summary' => $opening->summary,
                'description' => $opening->description,
                'requirements' => $opening->requirements ?? [],
            ]);

        return $openings->isNotEmpty() ? $openings : $fallback;
    }

    public static function image(string $id, int $width = 1400): string
    {
        return "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$width}&q=82";
    }

    public static function assetPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            return asset(ltrim($path, '/'));
        }

        if (Str::startsWith($path, ['/'])) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public static function menuFallbackAvatar(string $title): string
    {
        $initials = collect(explode(' ', $title))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return 'https://ui-avatars.com/api/?name=' . urlencode($initials ?: $title) . '&background=14110d&color=f4d58d&size=600&bold=true';
    }

    public static function branchFallbackAvatar(string $name): string
    {
        $initials = collect(explode(' ', $name))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return 'https://ui-avatars.com/api/?name=' . urlencode($initials ?: $name) . '&background=1f1a14&color=f7ead1&size=900&bold=true';
    }

    private static function presentMenuItem(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'slug' => $item->slug ?: Str::slug($item->name),
            'category' => $item->category?->slug ?: Str::slug($item->category?->name ?? 'Signature'),
            'category_name' => $item->category?->name ?? 'Signature',
            'title' => $item->name,
            'description' => $item->description ?: 'A house favorite prepared with detail, restraint and memorable flavor.',
            'price' => $item->price ? number_format((float) $item->price, 2) : null,
            'image' => self::assetPath($item->image_url) ?: self::menuFallbackAvatar($item->name),
            'branch' => $item->branch?->name,
            'branch_slug' => $item->branch?->slug,
            'module' => $item->module?->name,
        ];
    }

    private static function presentBranch(Branch $branch, array $fallback): array
    {
        $settings = $branch->settings ?? [];

        return [
            'id' => $branch->id,
            'name' => $branch->name,
            'slug' => $branch->slug,
            'location' => $branch->location ?? $fallback['location'],
            'description' => $settings['description'] ?? $fallback['description'],
            'short_description' => $settings['short_description'] ?? $fallback['short_description'],
            'hours' => $settings['hours'] ?? $fallback['hours'],
            'phone' => $branch->phone ?? $fallback['phone'],
            'email' => $branch->email,
            'whatsapp' => $settings['whatsapp'] ?? $branch->phone ?? $fallback['whatsapp'],
            'image' => self::assetPath($settings['featured_image'] ?? null) ?: self::branchFallbackAvatar($branch->name),
            'hero_image' => self::assetPath($settings['hero_image'] ?? $settings['featured_image'] ?? null) ?: ($fallback['hero_image'] ?? self::branchFallbackAvatar($branch->name)),
            'map_url' => $settings['map_url'] ?? null,
            'directions_url' => $settings['directions_url'] ?? null,
            'tags' => $settings['tags'] ?? $fallback['tags'],
            'latitude' => $branch->latitude,
            'longitude' => $branch->longitude,
        ];
    }

    private static function fallbackBranches(): array
    {
        return [
            [
                'id' => null,
                'name' => 'Cazera Victoria Island',
                'slug' => 'victoria-island',
                'location' => 'Victoria Island, Lagos',
                'description' => 'A cinematic dining and lounge destination shaped for elevated evenings, polished hospitality and intimate celebrations.',
                'short_description' => 'Dining, lounge and refined night-out energy in the heart of the city.',
                'hours' => 'Mon-Sun, 10:00 AM - 12:00 AM',
                'phone' => '+234 800 000 0000',
                'email' => null,
                'whatsapp' => '+234 800 000 0000',
                'image' => self::image('photo-1555396273-367ea4eb4db5', 1000),
                'hero_image' => self::image('photo-1514933651103-005eec06c04b', 1800),
                'map_url' => null,
                'directions_url' => 'https://www.google.com/maps/search/?api=1&query=Victoria+Island+Lagos',
                'tags' => ['Restaurant', 'Lounge', 'Events'],
            ],
            [
                'id' => null,
                'name' => 'Cazera Lekki',
                'slug' => 'lekki',
                'location' => 'Lekki Phase 1, Lagos',
                'description' => 'A warm, immersive hospitality space for premium dining, private groups and relaxed late-night conversations.',
                'short_description' => 'Warm interiors, chef signatures and a social rhythm made for Lekki nights.',
                'hours' => 'Tue-Sun, 11:00 AM - 1:00 AM',
                'phone' => '+234 801 000 0000',
                'email' => null,
                'whatsapp' => '+234 801 000 0000',
                'image' => self::image('photo-1544148103-0773bf10d330', 1000),
                'hero_image' => self::image('photo-1550966871-3ed3cdb5ed0c', 1800),
                'map_url' => null,
                'directions_url' => 'https://www.google.com/maps/search/?api=1&query=Lekki+Phase+1+Lagos',
                'tags' => ['VIP', 'Dining', 'Nightlife'],
            ],
            [
                'id' => null,
                'name' => 'Cazera Ikeja',
                'slug' => 'ikeja',
                'location' => 'Ikeja GRA, Lagos',
                'description' => 'A graceful hospitality address for business lunches, family dining, weekend events and unhurried evenings.',
                'short_description' => 'Editorial calm, generous service and polished plates for every kind of visit.',
                'hours' => 'Mon-Sun, 9:00 AM - 11:00 PM',
                'phone' => '+234 802 000 0000',
                'email' => null,
                'whatsapp' => '+234 802 000 0000',
                'image' => self::image('photo-1552566626-52f8b828add9', 1000),
                'hero_image' => self::image('photo-1500530855697-b586d89ba3ee', 1800),
                'map_url' => null,
                'directions_url' => 'https://www.google.com/maps/search/?api=1&query=Ikeja+GRA+Lagos',
                'tags' => ['Family', 'Corporate', 'Events'],
            ],
        ];
    }

    private static function fallbackCategories(): array
    {
        return [
            ['id' => null, 'slug' => 'signature-plates', 'name' => 'Signature Plates', 'description' => 'House dishes with memorable flavor and elegant presentation.', 'image' => self::image('photo-1543352634-a1c51d9f1fa7', 900)],
            ['id' => null, 'slug' => 'lounge-drinks', 'name' => 'Lounge Drinks', 'description' => 'Craft cocktails, zero-proof pours and evening classics.', 'image' => self::image('photo-1551024709-8f23befc6f87', 900)],
            ['id' => null, 'slug' => 'private-events', 'name' => 'Private Events', 'description' => 'Curated hosting for birthdays, brand nights and intimate groups.', 'image' => self::image('photo-1511795409834-ef04bbd61622', 900)],
        ];
    }

    private static function fallbackMenuItems(): array
    {
        return [
            ['id' => null, 'slug' => 'charcoal-grilled-fillet', 'category' => 'signature-plates', 'category_name' => 'Signature Plates', 'title' => 'Charcoal Grilled Fillet', 'description' => 'Smoked butter, garden herbs and a velvet pepper jus.', 'price' => '28,500', 'image' => self::image('photo-1544025162-d76694265947', 900)],
            ['id' => null, 'slug' => 'cazera-seafood-rice', 'category' => 'signature-plates', 'category_name' => 'Signature Plates', 'title' => 'Cazera Seafood Rice', 'description' => 'Prawns, calamari, fragrant stock and citrus herb finish.', 'price' => '22,000', 'image' => self::image('photo-1533777857889-4be7c70b33f7', 900)],
            ['id' => null, 'slug' => 'golden-hour-spritz', 'category' => 'lounge-drinks', 'category_name' => 'Lounge Drinks', 'title' => 'Golden Hour Spritz', 'description' => 'Bright, crisp and built for slow evening conversation.', 'price' => '9,500', 'image' => self::image('photo-1513558161293-cdaf765ed2fd', 900)],
            ['id' => null, 'slug' => 'vip-shared-table', 'category' => 'private-events', 'category_name' => 'Private Events', 'title' => 'VIP Shared Table', 'description' => 'A chef-led spread for intimate celebrations and hosting.', 'price' => null, 'image' => self::image('photo-1555244162-803834f70033', 900)],
        ];
    }

    private static function fallbackTestimonials(): array
    {
        return [
            ['name' => 'Adaora N.', 'title' => 'Guest', 'quote' => 'The room, the service and the food all felt intentional. It is the kind of place you plan another visit before leaving.', 'rating' => 5],
            ['name' => 'Tunde A.', 'title' => 'Private event host', 'quote' => 'Our celebration felt effortless. The team understood the mood we wanted and made every guest feel expected.', 'rating' => 5],
            ['name' => 'Mira O.', 'title' => 'Regular guest', 'quote' => 'Cazera has that rare mix of warmth and polish. Beautiful without feeling cold.', 'rating' => 5],
        ];
    }
}
