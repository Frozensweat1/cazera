<?php

namespace App\Livewire\Backoffice\InventoryItems;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterCategory = '';
    public $filterSupplier = '';
    public $filterStatus = '';

    public $itemId;
    public $branch_id;
    public $module_id;
    public $category_id;
    public $supplier_id;
    public $name;
    public $slug;
    public $sku;
    public $barcode;
    public $description;
    public $unit_cost = 0;
    public $unit_price = 0;
    public $is_trackable = true;
    public $is_active = true;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'required|exists:modules,id',
            'category_id' => 'required|exists:inventory_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('inventory_items', 'slug')
                    ->where(fn ($query) => $query
                        ->where('branch_id', $this->branch_id)
                        ->where('module_id', $this->module_id)
                        ->where('category_id', $this->category_id))
                    ->ignore($this->itemId),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('inventory_items', 'sku')
                    ->where(fn ($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($this->itemId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('inventory_items', 'barcode')
                    ->where(fn ($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($this->itemId),
            ],
            'description' => 'nullable|string',
            'unit_cost' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'is_trackable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.inventory-items.index', [
            'inventoryItems' => InventoryItem::with(['branch', 'module', 'category', 'supplier'])
                ->accessible()
                ->when(
                    $this->search,
                    fn($query) => $query->where(fn ($query) => $query
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%"))
                )
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterCategory, fn($query) => $query->where('category_id', $this->filterCategory))
                ->when($this->filterSupplier, fn($query) => $query->where('supplier_id', $this->filterSupplier))
                ->when($this->filterStatus, fn($query) => $query->where('is_active', $this->filterStatus === 'active'))
                ->latest()
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),
            'filterCategories' => InventoryCategory::accessible()
                ->where('is_active', true)
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->orderBy('name')
                ->get(),
            'filterSuppliers' => Supplier::accessible()
                ->where('is_active', true)
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->orderBy('name')
                ->get(),
            'formModules' => $this->branch_id
                ? $this->accessibleModules((int) $this->branch_id)
                : collect(),
            'formCategories' => InventoryCategory::accessible()
                ->where('is_active', true)
                ->when($this->branch_id, fn($query) => $query->where('branch_id', $this->branch_id))
                ->when($this->module_id, fn($query) => $query->where('module_id', $this->module_id))
                ->orderBy('name')
                ->get(),
            'formSuppliers' => Supplier::accessible()
                ->where('is_active', true)
                ->when($this->branch_id, fn($query) => $query->where('branch_id', $this->branch_id))
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
        $this->filterSupplier = '';
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

    public function updatedFilterSupplier(): void
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
        $this->supplier_id = null;
    }

    public function updatedModuleId(): void
    {
        $this->category_id = null;
    }

    public function updatedName()
    {
        if (!$this->itemId) {
            $this->slug = $this->slug ?? str()->slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'itemId',
            'branch_id',
            'module_id',
            'category_id',
            'supplier_id',
            'name',
            'slug',
            'sku',
            'barcode',
            'description',
            'unit_cost',
            'unit_price',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->is_trackable = true;
        $this->is_active = true;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'inventory-item-form');
    }

    public function edit($id)
    {
        $item = InventoryItem::accessible()->findOrFail($id);

        $this->itemId = $item->id;
        $this->branch_id = $item->branch_id;
        $this->module_id = $item->module_id;
        $this->category_id = $item->category_id;
        $this->supplier_id = $item->supplier_id;
        $this->name = $item->name;
        $this->slug = $item->slug;
        $this->sku = $item->sku;
        $this->barcode = $item->barcode;
        $this->description = $item->description;
        $this->unit_cost = $item->unit_cost;
        $this->unit_price = $item->unit_price;
        $this->is_trackable = $item->is_trackable;
        $this->is_active = $item->is_active;

        $this->dispatch('open-modal', 'inventory-item-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        $category = InventoryCategory::accessible()->findOrFail($this->category_id);
        abort_unless((int) $category->branch_id === (int) $this->branch_id, 422, 'The category must belong to the selected branch.');
        abort_unless((string) $category->module_id === (string) $this->module_id, 422, 'The category must belong to the selected module.');

        if ($this->supplier_id) {
            $supplier = Supplier::accessible()->findOrFail($this->supplier_id);
            abort_unless((int) $supplier->branch_id === (int) $this->branch_id, 422, 'The supplier must belong to the selected branch.');
        }

        InventoryItem::updateOrCreate([
            'id' => $this->itemId,
        ], [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'unit_cost' => $this->unit_cost,
            'unit_price' => $this->unit_price,
            'is_trackable' => $this->is_trackable,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('close-modal', 'inventory-item-form');

        LivewireAlert::title('Inventory Item Saved')
            ->text('Inventory item saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        LivewireAlert::title('Delete Inventory Item')
            ->text('Are you sure you want to delete this inventory item?')
            ->asConfirm()
            ->onConfirm('delete', ['id' => $id])
            ->show();
    }

    public function delete($id)
    {
        $id = is_array($id) ? $id['id'] : $id;
        InventoryItem::accessible()->findOrFail($id)->delete();

        LivewireAlert::title('Inventory Item Deleted')
            ->text('Inventory item deleted successfully.')
            ->success()
            ->show();
    }
}
