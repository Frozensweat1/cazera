<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.website')]
class Gallery extends Component
{
    public string $category = 'all';

    public function mount(?string $category = null): void
    {
        $this->category = $category ?: 'all';
    }

    public function render()
    {
        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');
        $items = WebsiteContent::gallery();

        return view('livewire.website.gallery', [
            'page' => WebsiteContent::page('gallery'),
            'items' => $this->category === 'all' ? $items : $items->where('category', $this->category)->values(),
            'categories' => $items->pluck('category')->unique()->values(),
        ])->title('Gallery | ' . $brand);
    }
}
