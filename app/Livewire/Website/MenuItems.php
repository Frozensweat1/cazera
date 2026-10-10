<?php

namespace App\Livewire\Website;

use App\Models\MenuItem;
use App\Models\Module;
use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.website')]
class MenuItems extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search = '';

    public ?string $module = null;

    public int $perPage = 9;

    public int $page = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'module' => ['except' => null],
        'page' => ['except' => 1],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingModule(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');
        $pageData = WebsiteContent::page('menu');

        $availableStatuses = ['active', 'available', 'published'];
        $search = trim($this->search);

        $menuItems = MenuItem::query()
            ->with(['category', 'branch', 'module'])
            ->whereIn('status', $availableStatuses)
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('branch', fn ($query) => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%"));
            }))
            ->when($this->module, fn ($query) => $query->whereHas(
                'module',
                fn ($query) => $query->where('name', $this->module)
            ))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($this->perPage)
            ->through(fn (MenuItem $item) => WebsiteContent::presentMenuItem($item));

        $modules = Module::query()
            ->where('is_active', true)
            ->whereIn('id', MenuItem::query()
                ->select('module_id')
                ->whereNotNull('module_id')
                ->whereIn('status', $availableStatuses))
            ->orderBy('name')
            ->pluck('name');

        return view('livewire.website.menu-items', [
            'menuItems' => $menuItems,
            'modules' => $modules,
            'pageData' => $pageData,
        ])->layoutData(WebsiteContent::seoData($pageData, 'Menu | '.$brand));
    }
}
