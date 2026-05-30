<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Layout('components.layouts.website')]
class BranchShow extends Component
{
    public string $slug;
    public string $category = 'all';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $branch = WebsiteContent::branch($this->slug);

        if (! $branch) {
            throw new NotFoundHttpException();
        }

        $categories = WebsiteContent::categories($branch['id']);
        $menuItems = WebsiteContent::menuItems($branch['id']);
        $filteredItems = $this->category === 'all'
            ? $menuItems
            : $menuItems->where('category', $this->category)->values();

        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');

        return view('livewire.website.branch-show', [
            'branch' => $branch,
            'branches' => WebsiteContent::branches()->where('slug', '!=', $branch['slug'])->take(3),
            'categories' => $categories,
            'menuItems' => $filteredItems,
            'gallery' => WebsiteContent::gallery()->take(6),
            'testimonials' => WebsiteContent::testimonials($branch['id']),
            'reviews' => WebsiteContent::reviews($branch['id']),
        ])->title($branch['name'] . ' | ' . $brand);
    }
}
