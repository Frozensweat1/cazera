<?php

namespace App\Livewire\Backoffice\ExpenseCategories;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\ExpenseCategory;
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
    public $name;
    public $description;
    public $is_active = true;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'required|exists:modules,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')
                    ->where(fn ($query) => $query
                        ->where('branch_id', $this->branch_id)
                        ->where('module_id', $this->module_id))
                    ->ignore($this->categoryId),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.expense-categories.index', [
            'expenseCategories' => ExpenseCategory::with(['branch', 'module'])
                ->accessible()
                ->when($this->search, fn($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->latest()
                ->paginate(10),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $this->branch_id ?: null),
        ]);
    }

    public function updatedFilterBranch()
    {
        $this->filterModule = '';
    }

    public function updatedBranchId()
    {
        $this->module_id = '';
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'expense-category-form');
    }

    public function edit($id)
    {
        $category = ExpenseCategory::accessible()->findOrFail($id);

        $this->categoryId = $category->id;
        $this->branch_id = $category->branch_id;
        $this->module_id = $category->module_id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->is_active = $category->is_active;

        $this->dispatch('open-modal', 'expense-category-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        ExpenseCategory::updateOrCreate([
            'id' => $this->categoryId,
        ], [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->resetForm();
        $this->dispatch('close-modal', 'expense-category-form');

        LivewireAlert::title('Expense Category Saved')
            ->text('Expense category has been saved successfully.')
            ->success()
            ->show();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('open-modal', 'delete-expense-category-' . $id);
    }

    public function delete($id)
    {
        ExpenseCategory::accessible()->findOrFail($id)->delete();

        $this->dispatch('close-modal', 'delete-expense-category-' . $id);

        LivewireAlert::title('Deleted')
            ->text('Expense category deleted successfully.')
            ->success()
            ->show();
    }

    public function resetForm()
    {
        $this->reset([
            'categoryId',
            'module_id',
            'name',
            'description',
            'is_active',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->is_active = true;
    }
}
