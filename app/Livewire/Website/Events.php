<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.website')]
class Events extends Component
{
    public function render()
    {
        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');

        return view('livewire.website.events', [
            'page' => WebsiteContent::page('events'),
            'events' => WebsiteContent::events(),
        ])->title('Promotions & Events | ' . $brand);
    }
}
