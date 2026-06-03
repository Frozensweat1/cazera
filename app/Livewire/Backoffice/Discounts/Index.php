<?php

namespace App\Livewire\Backoffice\Discounts;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Discount;
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

    public $discountId;
    public $branch_id;
    public $module_id;
    public $name;
    public $code;
    public $type = 'percentage';
    public $value = 0;
    public $minimum_bill_amount = 0;
    public $maximum_discount_amount;
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
                Rule::unique('discounts', 'name')
                    ->where(fn ($query) => $query
                        ->where('branch_id', $this->branch_id)
                        ->where('module_id', $this->module_id))
                    ->ignore($this->discountId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('discounts', 'code')
                    ->where(fn ($query) => $query
                        ->where('branch_id', $this->branch_id)
                        ->where('module_id', $this->module_id))
                    ->ignore($this->discountId),
            ],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => [
                'required',
                'numeric',
                'min:0.01',
                $this->type === 'percentage' ? 'max:100' : 'max:999999999',
            ],
            'minimum_bill_amount' => 'required|numeric|min:0',
            'maximum_discount_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.discounts.index', [
            'discounts' => Discount::with(['branch', 'module'])
                ->accessible()
                ->when($this->search, fn ($query) => $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
                }))
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
        $this->dispatch('open-modal', 'discount-form');
    }

    public function edit(int $id): void
    {
        $discount = Discount::accessible()->findOrFail($id);

        $this->discountId = $discount->id;
        $this->branch_id = $discount->branch_id;
        $this->module_id = $discount->module_id;
        $this->name = $discount->name;
        $this->code = $discount->code;
        $this->type = $discount->type;
        $this->value = $discount->value;
        $this->minimum_bill_amount = $discount->minimum_bill_amount;
        $this->maximum_discount_amount = $discount->maximum_discount_amount;
        $this->description = $discount->description;
        $this->is_active = $discount->is_active;
        $this->starts_at = $discount->starts_at?->format('Y-m-d\TH:i');
        $this->ends_at = $discount->ends_at?->format('Y-m-d\TH:i');

        $this->dispatch('open-modal', 'discount-form');
    }

    public function save(): void
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        Discount::updateOrCreate(['id' => $this->discountId], [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'name' => $this->name,
            'code' => $this->code ?: null,
            'type' => $this->type,
            'value' => $this->value,
            'minimum_bill_amount' => $this->minimum_bill_amount,
            'maximum_discount_amount' => $this->maximum_discount_amount ?: null,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
        ]);

        $this->resetForm();
        $this->dispatch('close-modal', 'discount-form');

        LivewireAlert::title('Discount Saved')
            ->text('Discount rule has been saved successfully.')
            ->success()
            ->show();
    }

    public function confirmDelete(int $id): void
    {
        $this->dispatch('open-modal', 'delete-discount-' . $id);
    }

    public function delete(int $id): void
    {
        Discount::accessible()->findOrFail($id)->delete();
        $this->dispatch('close-modal', 'delete-discount-' . $id);

        LivewireAlert::title('Deleted')
            ->text('Discount rule deleted successfully.')
            ->success()
            ->show();
    }

    public function resetForm(): void
    {
        $this->reset([
            'discountId',
            'module_id',
            'name',
            'code',
            'type',
            'value',
            'minimum_bill_amount',
            'maximum_discount_amount',
            'description',
            'is_active',
            'starts_at',
            'ends_at',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->type = 'percentage';
        $this->value = 0;
        $this->minimum_bill_amount = 0;
        $this->is_active = true;
    }
}
