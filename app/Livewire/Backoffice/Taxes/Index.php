<?php

namespace App\Livewire\Backoffice\Taxes;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Tax;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use HasBranchScope;
    use WithPagination;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';

    public $taxId;
    public $branch_id;
    public $module_id;
    public $name;
    public $rate_percent = 0;
    public $description;
    public $is_active = true;
    public $starts_at;
    public $ends_at;

    protected function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'required|exists:modules,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes', 'name')
                    ->where(fn ($query) => $query
                        ->where('branch_id', $this->branch_id)
                        ->where('module_id', $this->module_id))
                    ->ignore($this->taxId),
            ],
            'rate_percent' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.taxes.index', [
            'taxes' => Tax::with(['branch', 'module'])
                ->accessible()
                ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
                ->latest()
                ->paginate(10),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $this->branch_id ?: null),
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
        $this->resetPage();
    }

    public function updatedBranchId(): void
    {
        $this->module_id = '';
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'tax-form');
    }

    public function edit(int $id): void
    {
        $tax = Tax::accessible()->findOrFail($id);

        $this->taxId = $tax->id;
        $this->branch_id = $tax->branch_id;
        $this->module_id = $tax->module_id;
        $this->name = $tax->name;
        $this->rate_percent = $tax->rate_percent;
        $this->description = $tax->description;
        $this->is_active = $tax->is_active;
        $this->starts_at = $tax->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $tax->ends_at?->format('Y-m-d\TH:i');

        $this->dispatch('open-modal', 'tax-form');
    }

    public function save(): void
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        Tax::updateOrCreate(['id' => $this->taxId], [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'name' => $this->name,
            'rate_percent' => $this->rate_percent,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
        ]);

        $this->resetForm();
        $this->dispatch('close-modal', 'tax-form');

        LivewireAlert::title('Tax Saved')
            ->text('Tax rule has been saved successfully.')
            ->success()
            ->show();
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('open-modal', 'delete-tax-' . $id);
    }

    public function delete(int $id): void
    {
        Tax::accessible()->findOrFail($id)->delete();
        $this->dispatch('close-modal', 'delete-tax-' . $id);

        LivewireAlert::title('Deleted')
            ->text('Tax rule deleted successfully.')
            ->success()
            ->show();
    }

    public function resetForm(): void
    {
        $this->reset([
            'taxId',
            'module_id',
            'name',
            'rate_percent',
            'description',
            'is_active',
            'starts_at',
            'ends_at',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->rate_percent = 0;
        $this->is_active = true;
    }
}
