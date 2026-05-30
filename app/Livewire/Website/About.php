<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.website')]
class About extends Component
{
    public function render()
    {
        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');

        return view('livewire.website.about', [
            'page' => WebsiteContent::page('about'),
            'branches' => WebsiteContent::branches(),
        ])->title('About | ' . $brand);
    }
}
