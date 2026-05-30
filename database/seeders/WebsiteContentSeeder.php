<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CareerOpening;
use App\Models\GalleryItem;
use App\Models\Testimonial;
use App\Models\WebsiteEvent;
use App\Models\WebsitePage;
use App\Models\WebsiteSetting;
use App\Support\WebsiteContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedPages();
        $this->seedFeaturedContent();
    }

    private function seedSettings(): void
    {
        $settings = WebsiteSetting::query()->firstOrNew([]);

        $settings->fill([
            'business_name' => $settings->business_name ?: 'Cazera',
            'tagline' => $settings->tagline ?: 'Premium hospitality, memorable nights.',
            'email' => $settings->email ?: 'hello@cazera.test',
            'phone' => $settings->phone ?: '+233 24 000 0000',
            'whatsapp' => $settings->whatsapp ?: '+233 24 000 0000',
            'address' => $settings->address ?: 'Gumani, Tamale, Ghana',
            'meta_title' => $settings->meta_title ?: 'Cazera | Premium Restaurant, Bar and Lounge',
            'meta_description' => $settings->meta_description ?: 'Explore Cazera branches, signature menus, galleries, guest reviews, events and direct contact options.',
            'content' => array_replace_recursive($this->homepageContent(), $settings->content ?? []),
        ]);

        $settings->save();
    }

    private function seedPages(): void
    {
        foreach ($this->pages() as $index => $page) {
            WebsitePage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                array_merge($page, [
                    'sort_order' => $index + 1,
                    'is_published' => true,
                ])
            );
        }
    }

    private function seedFeaturedContent(): void
    {
        $branch = Branch::query()->orderBy('id')->first();

        foreach ($this->events($branch?->id) as $index => $event) {
            WebsiteEvent::query()->updateOrCreate(
                ['slug' => $event['slug']],
                array_merge($event, ['sort_order' => $index + 1])
            );
        }

        foreach ($this->gallery($branch?->id) as $index => $item) {
            GalleryItem::query()->updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, ['sort_order' => $index + 1])
            );
        }

        foreach ($this->careers($branch?->id) as $index => $opening) {
            CareerOpening::query()->updateOrCreate(
                ['slug' => $opening['slug']],
                array_merge($opening, ['sort_order' => $index + 1])
            );
        }

        foreach ($this->testimonials($branch?->id) as $index => $testimonial) {
            Testimonial::query()->updateOrCreate(
                [
                    'author_name' => $testimonial['author_name'],
                    'quote' => $testimonial['quote'],
                ],
                array_merge($testimonial, ['sort_order' => $index + 1])
            );
        }
    }

    private function homepageContent(): array
    {
        return [
            'brand' => [
                'name' => 'Cazera',
            ],
            'homepage' => [
                'hero_eyebrow' => 'Premium hospitality across every branch',
                'hero_title' => 'Cazera',
                'hero_subtitle' => 'Step into refined dining, atmospheric lounges, signature plates, private celebrations and nights that linger long after the last toast.',
                'branch_selector_eyebrow' => 'Find your atmosphere',
                'branch_selector_title' => 'Choose the room, rhythm and location that fit tonight.',
                'featured_branches_eyebrow' => 'Featured branches',
                'featured_branches_title' => 'Each branch has its own rhythm.',
                'featured_branches_subtitle' => 'From polished dining rooms to late-evening lounges, every location is shaped for a visit worth planning.',
                'categories_eyebrow' => 'Services and categories',
                'categories_title' => 'Designed for dining, hosting and beautiful evenings.',
                'categories_subtitle' => 'Explore the moods of the house, from chef-led plates and crafted drinks to private moments made for the right table.',
                'menu_eyebrow' => 'Menu highlights',
                'menu_title' => 'Signature tastes, beautifully introduced.',
                'menu_subtitle' => 'A curated first look at plates, pours and experiences guests come back for.',
                'about_eyebrow' => 'About Cazera',
                'about_title' => 'Hospitality should feel remembered, not merely served.',
                'about_body' => 'Cazera is built around warm service, intentional rooms and food that feels personal. Every branch carries its own atmosphere, but the promise stays the same: arrive curious, leave already thinking about the next visit.',
                'testimonials_eyebrow' => 'Guest voices',
                'testimonials_title' => 'Trust, told with warmth.',
                'testimonials_subtitle' => 'The best stories are the ones guests tell after the table has been cleared.',
                'gallery_eyebrow' => 'Gallery preview',
                'gallery_title' => 'A visual invitation.',
                'gallery_subtitle' => 'Ambience, plates, private rooms and evening energy, captured so guests can feel the place before they arrive.',
                'events_eyebrow' => 'Promotions and events',
                'events_title' => 'Reasons to come tonight, this week, this season.',
                'events_subtitle' => 'Live music, golden-hour moments, chef specials and seasonal invitations from the branches.',
                'cta_title' => 'Make the next visit feel inevitable.',
                'cta_subtitle' => 'Choose a branch, call the team, send a WhatsApp message or ask what is happening tonight.',
            ],
            'mobile_cta' => [
                'subtitle' => 'Call or send a WhatsApp message',
            ],
        ];
    }

    private function pages(): array
    {
        return [
            [
                'slug' => 'homepage',
                'eyebrow' => 'Premium hospitality across every branch',
                'title' => 'Cazera',
                'subtitle' => 'Refined dining, atmospheric lounges, signature plates and memorable nights across every branch.',
                'body' => 'Cazera brings together polished hospitality, expressive spaces and direct branch contact for guests who want to feel the place before they arrive.',
                'hero_image' => WebsiteContent::image('photo-1514933651103-005eec06c04b', 1900),
                'sections' => Arr::get($this->homepageContent(), 'homepage'),
                'meta_title' => 'Cazera | Premium Restaurant, Bar and Lounge',
                'meta_description' => 'Browse Cazera branches, menus, galleries, events, reviews and direct contact options.',
            ],
            [
                'slug' => 'about',
                'eyebrow' => 'Our story',
                'title' => 'A hospitality brand built around atmosphere, care and return visits.',
                'subtitle' => 'Cazera brings together generous service, expressive rooms and memorable food for guests who value the feeling of a place as much as the plate.',
                'body' => 'Every branch is shaped around warmth, detail and the small gestures that make guests feel expected. From the first welcome to the final goodbye, Cazera is built for visits people want to repeat.',
                'hero_image' => WebsiteContent::image('photo-1552566626-52f8b828add9', 1400),
                'sections' => [
                    'mission' => 'To make every branch feel welcoming before arrival and unforgettable after the visit.',
                    'vision' => 'To become a hospitality name known for atmosphere, consistency and beautifully hosted moments.',
                    'values' => ['Warmth', 'Craft', 'Consistency', 'Elegance'],
                ],
                'meta_title' => 'About Cazera',
                'meta_description' => 'Learn about Cazera hospitality, mission, values, branches and guest philosophy.',
            ],
            [
                'slug' => 'branches',
                'eyebrow' => 'Branches',
                'title' => 'Choose the atmosphere that fits tonight.',
                'subtitle' => 'Every branch carries its own mood, location, hours and direct ways to reach the team.',
                'body' => 'Explore Cazera locations and decide where your next dining, lounge or celebration moment should happen.',
                'hero_image' => WebsiteContent::image('photo-1544148103-0773bf10d330', 1400),
                'sections' => null,
                'meta_title' => 'Cazera Branches',
                'meta_description' => 'Find Cazera branches, locations, hours, calls, WhatsApp and directions.',
            ],
            [
                'slug' => 'gallery',
                'eyebrow' => 'Gallery',
                'title' => 'Ambience, plates, events and rooms worth entering.',
                'subtitle' => 'A visual look at the food, interiors, nightlife, private rooms and celebrations that shape the Cazera experience.',
                'body' => 'Browse the moments, rooms and details that give each branch its visual memory.',
                'hero_image' => WebsiteContent::image('photo-1517248135467-4c7edcad34c4', 1400),
                'sections' => ['categories' => ['ambiance', 'food', 'events', 'nightlife', 'VIP', 'interiors']],
                'meta_title' => 'Cazera Gallery',
                'meta_description' => 'View Cazera ambiance, food, events, nightlife, VIP and interior gallery highlights.',
            ],
            [
                'slug' => 'events',
                'eyebrow' => 'Promotions and events',
                'title' => 'What is happening at Cazera.',
                'subtitle' => 'Upcoming events, seasonal campaigns, happy hour moments and live music nights, presented as visual invitations.',
                'body' => 'Discover evenings, offers and branch moments worth planning around.',
                'hero_image' => WebsiteContent::image('photo-1492684223066-81342ee5ff30', 1400),
                'sections' => null,
                'meta_title' => 'Cazera Events and Promotions',
                'meta_description' => 'Explore Cazera promotions, events, live music, happy hour and seasonal campaigns.',
            ],
            [
                'slug' => 'careers',
                'eyebrow' => 'Careers',
                'title' => 'Join a team that treats hospitality as craft.',
                'subtitle' => 'Bring your presence, discipline and warmth to a team that believes every guest interaction should feel considered.',
                'body' => 'Cazera welcomes people who care about service, pace, presentation and the discipline behind beautiful hospitality.',
                'hero_image' => WebsiteContent::image('photo-1556761175-b413da4baf72', 1400),
                'sections' => ['culture' => ['Warm service', 'Growth mindset', 'Team discipline', 'Attention to detail']],
                'meta_title' => 'Careers at Cazera',
                'meta_description' => 'Explore hospitality careers and open roles at Cazera.',
            ],
            [
                'slug' => 'contact',
                'eyebrow' => 'Contact',
                'title' => 'Call, message or ask the right branch.',
                'subtitle' => 'Reach the team for general inquiries, private events, menu questions, media requests or feedback.',
                'body' => 'Use the form, call directly or send a WhatsApp message and the hospitality team will respond.',
                'hero_image' => WebsiteContent::image('photo-1551218808-94e220e084d2', 1400),
                'sections' => ['inquiries' => ['General inquiry', 'Private events', 'Menu questions', 'Feedback', 'Media request']],
                'meta_title' => 'Contact Cazera',
                'meta_description' => 'Contact Cazera branches by form, phone, WhatsApp, email or map.',
            ],
            [
                'slug' => 'reviews',
                'eyebrow' => 'Testimonials and reviews',
                'title' => 'Real guest confidence, presented with restraint.',
                'subtitle' => 'Browse published reviews, filter by branch or menu item, and share your own experience for moderation.',
                'body' => 'Guest stories help future visitors choose the branch, plate or evening that feels right.',
                'hero_image' => WebsiteContent::image('photo-1521017432531-fbd92d768814', 1400),
                'sections' => null,
                'meta_title' => 'Cazera Reviews and Testimonials',
                'meta_description' => 'Read Cazera reviews, testimonials and guest ratings.',
            ],
            [
                'slug' => 'mission',
                'eyebrow' => 'Mission',
                'title' => 'Hospitality that feels personal, polished and worth repeating.',
                'subtitle' => 'Every service moment should make guests feel expected, relaxed and ready to return.',
                'body' => 'Our mission is to create branch experiences where atmosphere, food and service work together with quiet confidence.',
                'hero_image' => WebsiteContent::image('photo-1559339352-11d035aa65de', 1400),
                'sections' => null,
                'meta_title' => 'Cazera Mission',
                'meta_description' => 'Cazera mission and hospitality philosophy.',
            ],
            [
                'slug' => 'vision',
                'eyebrow' => 'Vision',
                'title' => 'A growing hospitality name known for atmosphere and care.',
                'subtitle' => 'We imagine every branch as a familiar invitation with its own local character.',
                'body' => 'Our vision is to keep expanding without losing the warmth, discipline and emotional detail guests remember.',
                'hero_image' => WebsiteContent::image('photo-1500530855697-b586d89ba3ee', 1400),
                'sections' => null,
                'meta_title' => 'Cazera Vision',
                'meta_description' => 'Cazera vision for branch-led hospitality experiences.',
            ],
        ];
    }

    private function events(?int $branchId): array
    {
        return [
            [
                'branch_id' => $branchId,
                'slug' => 'friday-live-sessions',
                'title' => 'Friday Live Sessions',
                'tag' => 'Live music',
                'date_label' => 'Every Friday',
                'starts_at' => now()->next('Friday')->setTime(19, 0),
                'ends_at' => now()->next('Friday')->setTime(23, 30),
                'description' => 'Soulful performances, chef specials and a polished late-evening room.',
                'body' => 'Settle into live music, signature plates and a branch atmosphere shaped for unhurried Friday conversations.',
                'image' => WebsiteContent::image('photo-1492684223066-81342ee5ff30', 1200),
                'is_featured' => true,
                'is_published' => true,
            ],
            [
                'branch_id' => $branchId,
                'slug' => 'golden-hour-specials',
                'title' => 'Golden Hour Specials',
                'tag' => 'Happy hour',
                'date_label' => 'Weekdays, 5-7 PM',
                'starts_at' => now()->addDay()->setTime(17, 0),
                'ends_at' => now()->addDay()->setTime(19, 0),
                'description' => 'Signature drinks and small plates for an elegant pre-evening pause.',
                'body' => 'Arrive before the room gets loud and enjoy crafted pours, warm plates and golden-hour service.',
                'image' => WebsiteContent::image('photo-1513558161293-cdaf765ed2fd', 1200),
                'is_featured' => true,
                'is_published' => true,
            ],
            [
                'branch_id' => $branchId,
                'slug' => 'seasonal-tasting-week',
                'title' => 'Seasonal Tasting Week',
                'tag' => 'Campaign',
                'date_label' => 'This season',
                'starts_at' => now()->addWeek()->startOfDay(),
                'ends_at' => now()->addWeeks(2)->endOfDay(),
                'description' => 'A curated tasting journey across house favorites, new plates and celebratory pours.',
                'body' => 'Taste the branch through a seasonal edit of chef signatures, drink pairings and intimate service.',
                'image' => WebsiteContent::image('photo-1551218808-94e220e084d2', 1200),
                'is_featured' => true,
                'is_published' => true,
            ],
        ];
    }

    private function gallery(?int $branchId): array
    {
        return [
            ['branch_id' => $branchId, 'slug' => 'golden-hour-arrival', 'title' => 'Golden hour arrival', 'category' => 'ambiance', 'type' => 'image', 'image' => WebsiteContent::image('photo-1517248135467-4c7edcad34c4', 1200), 'description' => 'Warm first impressions before the evening begins.', 'is_featured' => true, 'is_published' => true],
            ['branch_id' => $branchId, 'slug' => 'chef-signatures', 'title' => 'Chef signatures', 'category' => 'food', 'type' => 'image', 'image' => WebsiteContent::image('photo-1543353071-873f17a7a088', 1200), 'description' => 'Plates shaped with balance, color and care.', 'is_featured' => true, 'is_published' => true],
            ['branch_id' => $branchId, 'slug' => 'private-celebrations', 'title' => 'Private celebrations', 'category' => 'events', 'type' => 'image', 'image' => WebsiteContent::image('photo-1511795409834-ef04bbd61622', 1200), 'description' => 'Tables made for birthdays, teams and intimate hosting.', 'is_featured' => true, 'is_published' => true],
            ['branch_id' => $branchId, 'slug' => 'after-dark-lounge', 'title' => 'After dark lounge', 'category' => 'nightlife', 'type' => 'image', 'image' => WebsiteContent::image('photo-1572116469696-31de0f17cc34', 1200), 'description' => 'A late room with music, light and relaxed energy.', 'is_featured' => true, 'is_published' => true],
            ['branch_id' => $branchId, 'slug' => 'reserved-experiences', 'title' => 'Reserved experiences', 'category' => 'VIP', 'type' => 'image', 'image' => WebsiteContent::image('photo-1566073771259-6a8506099945', 1200), 'description' => 'Discreet corners for a more private evening.', 'is_featured' => true, 'is_published' => true],
            ['branch_id' => $branchId, 'slug' => 'textured-interiors', 'title' => 'Textured interiors', 'category' => 'interiors', 'type' => 'image', 'image' => WebsiteContent::image('photo-1552566626-52f8b828add9', 1200), 'description' => 'Layered rooms with warmth, polish and detail.', 'is_featured' => true, 'is_published' => true],
        ];
    }

    private function careers(?int $branchId): array
    {
        return [
            [
                'branch_id' => $branchId,
                'role' => 'Guest Experience Host',
                'slug' => 'guest-experience-host',
                'location' => 'All branches',
                'employment_type' => 'Full-time',
                'summary' => 'Welcome guests, manage floor flow and support memorable service moments.',
                'description' => 'This role is for a calm, articulate host who can read the room, communicate clearly and make every guest feel expected.',
                'requirements' => ['Warm communication', 'Strong presence under pressure', 'Hospitality or front desk experience preferred'],
                'is_active' => true,
            ],
            [
                'branch_id' => $branchId,
                'role' => 'Chef de Partie',
                'slug' => 'chef-de-partie',
                'location' => 'Main kitchen',
                'employment_type' => 'Full-time',
                'summary' => 'Support preparation, plating consistency and kitchen discipline during service.',
                'description' => 'A detail-led kitchen role for someone who respects prep, timing, cleanliness and flavor consistency.',
                'requirements' => ['Kitchen experience', 'Prep discipline', 'Food safety awareness'],
                'is_active' => true,
            ],
            [
                'branch_id' => $branchId,
                'role' => 'Bar and Mixology Associate',
                'slug' => 'bar-and-mixology-associate',
                'location' => 'Lounge and bar',
                'employment_type' => 'Shift-based',
                'summary' => 'Prepare drinks, maintain bar presentation and support a polished evening rhythm.',
                'description' => 'A role for someone with good product knowledge, a clean station and a natural sense of guest pace.',
                'requirements' => ['Bar experience preferred', 'Clean presentation', 'Guest-focused attitude'],
                'is_active' => true,
            ],
        ];
    }

    private function testimonials(?int $branchId): array
    {
        return [
            [
                'branch_id' => $branchId,
                'module_id' => null,
                'author_name' => 'Adaora N.',
                'title' => 'Guest',
                'company' => null,
                'quote' => 'The room, the service and the food all felt intentional. It is the kind of place you plan another visit before leaving.',
                'rating' => 5,
                'is_published' => true,
                'is_featured' => true,
            ],
            [
                'branch_id' => $branchId,
                'module_id' => null,
                'author_name' => 'Tunde A.',
                'title' => 'Private event host',
                'company' => null,
                'quote' => 'Our celebration felt effortless. The team understood the mood we wanted and made every guest feel expected.',
                'rating' => 5,
                'is_published' => true,
                'is_featured' => true,
            ],
            [
                'branch_id' => $branchId,
                'module_id' => null,
                'author_name' => 'Mira O.',
                'title' => 'Regular guest',
                'company' => null,
                'quote' => 'Cazera has that rare mix of warmth and polish. Beautiful without feeling cold.',
                'rating' => 5,
                'is_published' => true,
                'is_featured' => true,
            ],
        ];
    }
}
