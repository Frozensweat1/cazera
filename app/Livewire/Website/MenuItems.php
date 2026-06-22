<?php

namespace App\Livewire\Website;

use App\Support\WebsiteContent;
use Illuminate\Pagination\LengthAwarePaginator;
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

        $all = WebsiteContent::menuItems(null, null)
            ->filter(fn (array $item) => blank($this->search)
                || str($item['title'] . ' ' . $item['description'] . ' ' . $item['category_name'] . ' ' . $item['branch'])
                    ->lower()
                    ->contains(str($this->search)->lower()))
            ->values();

        if ($this->module) {
            $all = $all->filter(fn (array $item) => ! empty($item['module']) && str($item['module'])->lower()->is(str($this->module)->lower()))->values();
        }

        $total = $all->count();
        $page = max(1, (int) $this->page);
        $slice = $all->slice(($page - 1) * $this->perPage, $this->perPage)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $total,
            $this->perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        $modules = WebsiteContent::menuItems(null, null)->pluck('module')->filter()->unique()->values();

        return view('livewire.website.menu-items', [
            'menuItems' => $paginator,
            'modules' => $modules,
            'pageData' => WebsiteContent::page('menu'),
        ])->title('Menu | ' . $brand);
    }
}
