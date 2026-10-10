<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Layout('components.layouts.website')]
class MenuItemShow extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $item = WebsiteContent::menuItem($this->slug);

        if (! $item) {
            throw new NotFoundHttpException;
        }

        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');

        return view('livewire.website.menu-item-show', [
            'item' => $item,
            'relatedItems' => WebsiteContent::randomMenuItems(8, $this->slug),
        ])->layoutData(WebsiteContent::seoData(
            [],
            $item['title'].' | '.$brand,
            $item['description'],
            $item['image'],
        ));
    }
}
