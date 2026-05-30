<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.website')]
class Branches extends Component
{
    public string $search = '';

    public function render()
    {
        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');
        $branches = WebsiteContent::branches()
            ->filter(fn (array $branch) => blank($this->search)
                || str($branch['name'] . ' ' . $branch['location'] . ' ' . $branch['description'])->lower()->contains(str($this->search)->lower()))
            ->values();

        return view('livewire.website.branches', [
            'branches' => $branches,
            'page' => WebsiteContent::page('branches'),
        ])->title('Branches | ' . $brand);
    }
}
