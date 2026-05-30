<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Layout('components.layouts.website')]
class EventShow extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $event = WebsiteContent::events()->firstWhere('slug', $this->slug);

        if (! $event) {
            throw new NotFoundHttpException();
        }

        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');

        return view('livewire.website.event-show', [
            'event' => $event,
            'events' => WebsiteContent::events()->where('slug', '!=', $this->slug),
        ])->title($event['title'] . ' | ' . $brand);
    }
}
