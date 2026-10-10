<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.website')]
class Home extends Component
{
    public function render()
    {
        $settings = WebsiteContent::settings();
        $brand = $settings?->business_name ?: config('app.name', 'Cazera');
        $page = WebsiteContent::page('homepage');

        return view('livewire.website.home', [
            'settings' => $settings,
            'page' => $page,
            'branches' => WebsiteContent::branches()->take(3),
            'categories' => WebsiteContent::categories()->take(3),
            'menuItems' => WebsiteContent::menuItems()->take(4),
            'testimonials' => WebsiteContent::testimonials()->take(3),
            'gallery' => WebsiteContent::gallery()->take(5),
            'events' => WebsiteContent::events()->take(3),
        ])->layoutData(WebsiteContent::seoData(
            $page,
            $settings?->meta_title ?: $brand.' | Premium Hospitality Experiences',
            $settings?->meta_description,
            WebsiteContent::assetPath($settings?->hero_background),
        ));
    }
}
