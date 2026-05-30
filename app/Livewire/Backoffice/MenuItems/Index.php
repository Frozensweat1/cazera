<?php

namespace App\Livewire\Backoffice\MenuItems;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuItemAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\WithFileUploads;

class Index extends Component
{

    use WithPagination;
    use WithFileUploads;
    use HasBranchScope;

    public $search = '';

    public $filterBranch = '';

    public $filterModule = '';

    public $filterCategory = '';

    public $filterStatus = '';

    public $selected = [];
    public $selectPage = false;

    public $menuItemId;

    public $branch_id;

    public $module_id;

    public $category_id;

    public $name;

    public $slug;

    public $description;

    public $image_url;

    public $image;

    public $quantity = 0;

    public $price;

    public $cost_price;

    public $preparation_time = 0;

    public $status = 'available';

    public $is_trackable = false;

    public $sort_order = 0;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',

            'module_id' => 'required|exists:modules,id',

            'category_id' => 'required|exists:categories,id',

            'name' => 'required|string|max:255',

            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('menu_items', 'slug')
                    ->where(fn ($query) => $query
                        ->where('branch_id', $this->branch_id)
                        ->where('module_id', $this->module_id)
                        ->where('category_id', $this->category_id))
                    ->ignore($this->menuItemId),
            ],

            'description' => 'nullable|string',

            'image_url' => 'nullable|string|max:2048',
            'image' => 'nullable|image|max:5120',

            'quantity' => 'nullable|integer|min:0',

            'price' => 'required|numeric|min:0',

            'cost_price' => 'nullable|numeric|min:0',

            'preparation_time' => 'nullable|integer|min:0',

            'status' => 'required|in:available,unavailable,out_of_stock',

            'is_trackable' => 'boolean',

            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.menu-items.index', [

            'menuItems' => $this->filteredMenuItemsQuery()
                ->with([
                'branch',
                'module',
                'category',
            ])
                ->orderBy('sort_order')
                ->latest()
                ->paginate(10),

            'branches' => $this->accessibleBranches(),

            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),

            'filterCategories' => Category::accessible()
                ->where('is_active', true)
                ->when($this->filterBranch, fn($q) => $q->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($q) => $q->where('module_id', $this->filterModule))
                ->orderBy('name')
                ->get(),

            'formModules' => $this->branch_id
                ? $this->accessibleModules((int) $this->branch_id)
                : collect(),

            'formCategories' => Category::accessible()
                ->where('is_active', true)
                ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
                ->when($this->module_id, fn($q) => $q->where('module_id', $this->module_id))
                ->orderBy('name')
                ->get(),

        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
        $this->filterCategory = '';
        $this->resetPage();
    }

    public function updatedFilterModule(): void
    {
        $this->filterCategory = '';
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedBranchId(): void
    {
        $this->module_id = null;
        $this->category_id = null;
    }

    public function updatedModuleId(): void
    {
        $this->category_id = null;
    }

    public function updatedImage(): void
    {
        $this->validateOnly('image');
    }

    public function updatedSelectPage($value): void
    {
        $this->selected = $value
            ? $this->filteredMenuItemsQuery()->pluck('id')->map(fn ($id) => (string) $id)->all()
            : [];
    }

    public function updatedName()
    {
        if (!$this->menuItemId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'menuItemId',
            'branch_id',
            'module_id',
            'category_id',
            'name',
            'slug',
            'description',
            'image_url',
            'image',
            'quantity',
            'price',
            'cost_price',
            'preparation_time',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->status = 'available';

        $this->is_trackable = false;

        $this->sort_order = 0;
    }

    public function create()
    {
        $this->resetForm();

        $this->dispatch('open-modal', 'menu-item-form');
    }

    public function edit($id)
    {
        $item = MenuItem::accessible()->findOrFail($id);

        $this->menuItemId = $item->id;

        $this->branch_id = $item->branch_id;

        $this->module_id = $item->module_id;

        $this->category_id = $item->category_id;

        $this->name = $item->name;

        $this->slug = $item->slug;

        $this->description = $item->description;

        $this->image_url = $item->image_url;

        $this->quantity = $item->quantity;

        $this->price = $item->price;

        $this->cost_price = $item->cost_price;

        $this->preparation_time = $item->preparation_time;

        $this->status = $item->status;

        $this->is_trackable = $item->is_trackable;

        $this->sort_order = $item->sort_order;

        $this->dispatch('open-modal', 'menu-item-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        $category = Category::accessible()->findOrFail($this->category_id);
        abort_unless((int) $category->branch_id === (int) $this->branch_id && (int) $category->module_id === (int) $this->module_id, 403);

        $existingItem = $this->menuItemId
            ? MenuItem::accessible()->findOrFail($this->menuItemId)
            : null;

        $imageUrl = $this->image_url;

        if ($this->image) {
            if ($existingItem) {
                $this->deleteStoredImage($existingItem->image_url);
            }

            $imageUrl = $this->image->store('menu-items', 'public');
        }

        $isTrackable = (bool) $this->is_trackable;
        $quantity = $isTrackable ? (int) ($this->quantity ?? 0) : 0;
        $status = $isTrackable && $quantity <= 0 ? 'out_of_stock' : $this->status;

        DB::transaction(function () use ($existingItem, $imageUrl, $isTrackable, $quantity, $status) {
            $item = MenuItem::updateOrCreate(
                ['id' => $this->menuItemId],
                [
                    'branch_id' => $this->branch_id,

                    'module_id' => $this->module_id,

                    'category_id' => $this->category_id,

                    'name' => $this->name,

                    'slug' => $this->slug,

                    'description' => $this->description,

                    'image_url' => $imageUrl,

                    'quantity' => $quantity,

                    'price' => $this->price,

                    'cost_price' => $this->cost_price,

                    'preparation_time' => $this->preparation_time,

                    'status' => $status,

                    'is_trackable' => $isTrackable,

                    'sort_order' => $this->sort_order,
                ]
            );

            $this->recordQuantityAdjustment($item, $existingItem, $isTrackable, $quantity);
        });

        $this->dispatch('close-modal', 'menu-item-form');

        LivewireAlert::title('Menu Item Saved')
            ->text('Menu item saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        LivewireAlert::title('Delete Menu Item')
            ->text('Are you sure you want to delete this menu item?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data)
    {
        $item = MenuItem::accessible()->findOrFail($data['id']);
        $this->deleteStoredImage($item->image_url);
        $item->delete();

        LivewireAlert::title('Deleted')
            ->text('Menu item deleted successfully.')
            ->success()
            ->show();
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selected)) {

            LivewireAlert::title('No Items Selected')
                ->text('Please select menu items to delete.')
                ->warning()
                ->show();

            return;
        }

        LivewireAlert::title('Bulk Delete')
            ->text('Are you sure you want to delete ' . count($this->selected) . ' menu items?')
            ->asConfirm()
            ->onConfirm('bulkDelete')
            ->show();
    }

    public function bulkDelete()
    {
        $items = MenuItem::accessible()->whereIn('id', $this->selected)->get();

        $items->each(function (MenuItem $item) {
            $this->deleteStoredImage($item->image_url);
            $item->delete();
        });

        $count = count($this->selected);

        $this->selected = [];
        $this->selectPage = false;

        LivewireAlert::title('Bulk Delete Complete')
            ->text($count . ' menu items deleted successfully.')
            ->success()
            ->show();
    }

    protected function filteredMenuItemsQuery()
    {
        return MenuItem::query()
            ->accessible()
            ->when($this->search, fn ($query) => $query
                ->where(fn ($query) => $query
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%")))
            ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->when($this->filterCategory, fn ($query) => $query->where('category_id', $this->filterCategory))
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus));
    }

    protected function deleteStoredImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://', '/storage/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    protected function recordQuantityAdjustment(MenuItem $item, ?MenuItem $existingItem, bool $isTrackable, int $quantity): void
    {
        $wasTrackable = (bool) ($existingItem?->is_trackable ?? false);
        $quantityBefore = $wasTrackable ? (int) ($existingItem?->quantity ?? 0) : 0;

        if (! $isTrackable && ! $wasTrackable) {
            return;
        }

        if (! $existingItem && $isTrackable) {
            $this->createMenuItemAdjustment($item, 'opening_stock', 0, $quantity, 'Initial menu item quantity');
            return;
        }

        if ($wasTrackable && ! $isTrackable) {
            if ($quantityBefore !== 0) {
                $this->createMenuItemAdjustment($item, 'manual_set', $quantityBefore, 0, 'Tracking disabled from menu item form');
            }

            return;
        }

        if (! $wasTrackable && $isTrackable) {
            $this->createMenuItemAdjustment($item, 'opening_stock', 0, $quantity, 'Tracking enabled from menu item form');
            return;
        }

        if ($quantityBefore === $quantity) {
            return;
        }

        $type = $quantity > $quantityBefore ? 'adjustment_increase' : 'adjustment_decrease';

        $this->createMenuItemAdjustment($item, $type, $quantityBefore, $quantity, 'Quantity updated from menu item form');
    }

    protected function createMenuItemAdjustment(MenuItem $item, string $type, int $quantityBefore, int $quantityAfter, string $reason): void
    {
        MenuItemAdjustment::create([
            'branch_id' => $item->branch_id,
            'module_id' => $item->module_id,
            'menu_item_id' => $item->id,
            'sale_id' => null,
            'performed_by' => auth()->id(),
            'type' => $type,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'change_qty' => $quantityAfter - $quantityBefore,
            'reference_no' => 'MI-' . now()->format('YmdHis'),
            'reason' => $reason,
            'notes' => null,
            'transaction_date' => now(),
        ]);
    }
}
