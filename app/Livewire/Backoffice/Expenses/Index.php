<?php

namespace App\Livewire\Backoffice\Expenses;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Expense;
use App\Models\ExpenseCategory;
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
    public $filterDate = '';

    public $expenseId;
    public $branch_id;
    public $module_id;
    public $expense_category_id;
    public $expense_date;
    public $title;
    public $amount = 0;
    public $notes;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'required|exists:modules,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.expenses.index', [
            'expenses' => Expense::with(['branch', 'module', 'category', 'recorder', 'locker'])
                ->accessible()
                ->when($this->search, fn($query) => $query->where('title', 'like', "%{$this->search}%"))
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterCategory, fn($query) => $query->where('expense_category_id', $this->filterCategory))
                ->when($this->filterDate, fn($query) => $query->where('expense_date', $this->filterDate))
                ->latest()
                ->paginate(10),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $this->branch_id ?: null),
            'categories' => ExpenseCategory::accessible()
                ->where('is_active', true)
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->orderBy('name')
                ->get(),
            'canManageLocks' => $this->canManageLocks(),
        ]);
    }

    public function updatedFilterBranch()
    {
        $this->filterModule = '';
        $this->filterCategory = '';
    }

    public function updatedBranchId()
    {
        $this->module_id = '';
        $this->expense_category_id = '';
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'expense-form');
    }

    public function edit($id)
    {
        $expense = Expense::accessible()->findOrFail($id);

        $this->expenseId = $expense->id;
        $this->branch_id = $expense->branch_id;
        $this->module_id = $expense->module_id;
        $this->expense_category_id = $expense->expense_category_id;
        $this->expense_date = $expense->expense_date->toDateString();
        $this->title = $expense->title;
        $this->amount = $expense->amount;
        $this->notes = $expense->notes;

        $this->dispatch('open-modal', 'expense-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        $category = ExpenseCategory::accessible()->findOrFail($this->expense_category_id);
        abort_unless((int) $category->branch_id === (int) $this->branch_id && (int) $category->module_id === (int) $this->module_id, 403);

        if ($this->expenseId) {
            $expense = Expense::accessible()->findOrFail($this->expenseId);

            if ($expense->is_locked) {
                LivewireAlert::title('Entry Locked')
                    ->text('This expense is locked and cannot be modified.')
                    ->warning()
                    ->show();

                return;
            }

            $expense->update([
                'branch_id' => $this->branch_id,
                'module_id' => $this->module_id,
                'expense_category_id' => $this->expense_category_id,
                'expense_date' => $this->expense_date,
                'title' => $this->title,
                'amount' => $this->amount,
                'notes' => $this->notes,
            ]);
        } else {
            Expense::create([
                'branch_id' => $this->branch_id,
                'module_id' => $this->module_id,
                'expense_category_id' => $this->expense_category_id,
                'recorded_by' => auth()->id(),
                'expense_date' => $this->expense_date,
                'title' => $this->title,
                'amount' => $this->amount,
                'notes' => $this->notes,
            ]);
        }

        $this->resetForm();
        $this->dispatch('close-modal', 'expense-form');

        LivewireAlert::title('Expense Saved')
            ->text('Expense has been saved successfully.')
            ->success()
            ->show();
    }

    public function lockExpense($id)
    {
        abort_unless($this->canManageLocks(), 403);

        $expense = Expense::accessible()->findOrFail($id);

        $expense->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ]);

        LivewireAlert::title('Locked')
            ->text('Expense entry has been locked.')
            ->success()
            ->show();
    }

    public function unlockExpense($id)
    {
        abort_unless($this->canManageLocks(), 403);

        $expense = Expense::accessible()->findOrFail($id);

        $expense->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        LivewireAlert::title('Unlocked')
            ->text('Expense entry has been unlocked.')
            ->success()
            ->show();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('open-modal', 'delete-expense-' . $id);
    }

    public function delete($id)
    {
        $expense = Expense::accessible()->findOrFail($id);

        if ($expense->is_locked) {
            LivewireAlert::title('Locked')
                ->text('Locked expenses cannot be deleted.')
                ->warning()
                ->show();

            return;
        }

        $expense->delete();

        $this->dispatch('close-modal', 'delete-expense-' . $id);

        LivewireAlert::title('Deleted')
            ->text('Expense deleted successfully.')
            ->success()
            ->show();
    }

    public function resetForm()
    {
        $this->reset([
            'expenseId',
            'module_id',
            'expense_category_id',
            'expense_date',
            'title',
            'amount',
            'notes',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->expense_date = now()->toDateString();
        $this->amount = 0;
    }

    protected function canManageLocks(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager']) ?? false;
    }
}
