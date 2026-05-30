<?php

namespace App\Livewire\Backoffice\InventoryCategories;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryCategory;
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
    public $categoryId;
    public $branch_id;
    public $module_id;
    public $parent_id;
    public $name;
    public $slug;
    public $description;
    public $is_active = true;
    public $sort_order = 0;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'parent_id' => 'nullable|exists:inventory_categories,id',
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('inventory_categories', 'slug')
                    ->where(fn($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($this->categoryId),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.inventory-categories.index', [
            'categories' => InventoryCategory::with(['branch', 'module', 'parent'])
                ->accessible()
                ->when(
                    $this->search,
                    fn($query) => $query->where(fn ($query) => $query
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%"))
                )
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->orderBy('sort_order')
                ->latest()
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),
            'formModules' => $this->branch_id
                ? $this->accessibleModules((int) $this->branch_id)
                : collect(),
            'parents' => InventoryCategory::accessible()
                ->where('is_active', true)
                ->when($this->branch_id, fn($query) => $query->where('branch_id', $this->branch_id))
                ->when($this->module_id, fn($query) => $query->where('module_id', $this->module_id))
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
        $this->resetPage();
    }

    public function updatedFilterModule(): void
    {
        $this->resetPage();
    }

    public function updatedBranchId(): void
    {
        $this->module_id = null;
        $this->parent_id = null;
    }

    public function updatedModuleId(): void
    {
        $this->parent_id = null;
    }

    public function updatedName()
    {
        if (!$this->categoryId) {
            $this->slug = $this->slug ?? str()->slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'categoryId',
            'branch_id',
            'module_id',
            'parent_id',
            'name',
            'slug',
            'description',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->is_active = true;
        $this->sort_order = 0;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'inventory-category-form');
    }

    public function edit($id)
    {
        $category = InventoryCategory::accessible()->findOrFail($id);

        $this->categoryId = $category->id;
        $this->branch_id = $category->branch_id;
        $this->module_id = $category->module_id;
        $this->parent_id = $category->parent_id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
        $this->is_active = $category->is_active;
        $this->sort_order = $category->sort_order;

        $this->dispatch('open-modal', 'inventory-category-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);

        if ($this->module_id) {
            $this->authorizeModule($this->module_id, $this->branch_id);
        }

        if ($this->parent_id) {
            $parent = InventoryCategory::accessible()->findOrFail($this->parent_id);
            abort_unless((int) $parent->branch_id === (int) $this->branch_id, 422, 'The parent category must belong to the selected branch.');
            abort_unless((string) $parent->module_id === (string) $this->module_id, 422, 'The parent category must belong to the selected module.');
        }

        InventoryCategory::updateOrCreate([
            'id' => $this->categoryId,
        ], [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ]);

        $this->dispatch('close-modal', 'inventory-category-form');

        LivewireAlert::title('Inventory Category Saved')
            ->text('Inventory category saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        LivewireAlert::title('Delete Inventory Category')
            ->text('Are you sure you want to delete this inventory category?')
            ->asConfirm()
            ->onConfirm('delete', ['id' => $id])
            ->show();
    }

    public function delete($id)
    {
        $id = is_array($id) ? $id['id'] : $id;
        InventoryCategory::accessible()->findOrFail($id)->delete();

        LivewireAlert::title('Inventory Category Deleted')
            ->text('Inventory category deleted successfully.')
            ->success()
            ->show();
    }
}
