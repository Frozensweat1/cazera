<?php

namespace App\Livewire\Backoffice\MenuItemAdjustments;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\MenuItem;
use App\Models\MenuItemAdjustment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public string $search = '';
    public string $filterBranch = '';
    public string $filterModule = '';
    public string $filterType = '';

    public ?int $adjustmentId = null;
    public $branch_id;
    public $module_id;
    public $menu_item_id;
    public string $type = 'adjustment_increase';
    public $quantity_before = 0;
    public $quantity_after = 0;
    public $reference_no;
    public $reason;
    public $notes;
    public $transaction_date;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'module_id' => ['required', 'exists:modules,id'],
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'type' => ['required', Rule::in($this->adjustmentTypes())],
            'quantity_before' => ['required', 'numeric', 'min:0'],
            'quantity_after' => ['required', 'numeric', 'min:0'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'transaction_date' => ['required', 'date'],
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.menu-item-adjustments.index', [
            'adjustments' => $this->adjustmentsQuery()
                ->with(['branch', 'module', 'menuItem', 'sale', 'performer'])
                ->orderByDesc('transaction_date')
                ->latest()
                ->paginate(10),
            'branches' => $this->accessibleBranches(),
            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),
            'formModules' => $this->branch_id
                ? $this->accessibleModules((int) $this->branch_id)
                : collect(),
            'formMenuItems' => MenuItem::accessible()
                ->where('is_trackable', true)
                ->where('status', '!=', 'unavailable')
                ->when($this->branch_id, fn (Builder $query) => $query->where('branch_id', $this->branch_id))
                ->when($this->module_id, fn (Builder $query) => $query->where('module_id', $this->module_id))
                ->orderBy('name')
                ->get(),
            'types' => $this->adjustmentTypes(),
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

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedBranchId(): void
    {
        $this->module_id = null;
        $this->menu_item_id = null;
        $this->quantity_before = 0;
        $this->quantity_after = 0;
    }

    public function updatedModuleId(): void
    {
        $this->menu_item_id = null;
        $this->quantity_before = 0;
        $this->quantity_after = 0;
    }

    public function updatedMenuItemId(): void
    {
        if (! $this->menu_item_id) {
            $this->quantity_before = 0;
            $this->quantity_after = 0;
            return;
        }

        $item = MenuItem::accessible()->findOrFail($this->menu_item_id);
        $this->authorizeBranch($item->branch_id);
        $this->authorizeModule($item->module_id, $item->branch_id);

        $this->branch_id = $item->branch_id;
        $this->module_id = $item->module_id;
        $this->quantity_before = $item->quantity ?? 0;
        $this->quantity_after = $item->quantity ?? 0;
    }

    public function resetForm(): void
    {
        $this->reset([
            'adjustmentId',
            'branch_id',
            'module_id',
            'menu_item_id',
            'reference_no',
            'reason',
            'notes',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->type = 'adjustment_increase';
        $this->quantity_before = 0;
        $this->quantity_after = 0;
        $this->transaction_date = now()->format('Y-m-d');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'menu-item-adjustment-form');
    }

    public function edit(int $id): void
    {
        $adjustment = MenuItemAdjustment::accessible()->findOrFail($id);

        abort_if($adjustment->sale_id, 403, 'Sale generated adjustments cannot be edited here.');

        $this->adjustmentId = $adjustment->id;
        $this->branch_id = $adjustment->branch_id;
        $this->module_id = $adjustment->module_id;
        $this->menu_item_id = $adjustment->menu_item_id;
        $this->type = $adjustment->type;
        $this->quantity_before = $adjustment->quantity_before;
        $this->quantity_after = $adjustment->quantity_after;
        $this->reference_no = $adjustment->reference_no;
        $this->reason = $adjustment->reason;
        $this->notes = $adjustment->notes;
        $this->transaction_date = $adjustment->transaction_date?->format('Y-m-d') ?: now()->format('Y-m-d');

        $this->dispatch('open-modal', 'menu-item-adjustment-form');
    }

    public function save(): void
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);
        $this->validateQuantityDirection();

        $item = MenuItem::accessible()->findOrFail($this->menu_item_id);

        abort_unless($item->is_trackable, 422, 'Only trackable menu items can be adjusted.');

        abort_unless(
            (int) $item->branch_id === (int) $this->branch_id
            && (int) $item->module_id === (int) $this->module_id,
            403
        );

        DB::transaction(function () use ($item) {
            $quantityBefore = $this->adjustmentId
                ? (float) $this->quantity_before
                : (float) ($item->quantity ?? 0);
            $quantityAfter = (float) $this->quantity_after;

            MenuItemAdjustment::updateOrCreate(
                ['id' => $this->adjustmentId],
                [
                    'branch_id' => $this->branch_id,
                    'module_id' => $this->module_id,
                    'menu_item_id' => $this->menu_item_id,
                    'sale_id' => null,
                    'performed_by' => auth()->id(),
                    'type' => $this->type,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'change_qty' => $quantityAfter - $quantityBefore,
                    'reference_no' => $this->reference_no ?: $this->generateReference(),
                    'reason' => $this->reason,
                    'notes' => $this->notes,
                    'transaction_date' => $this->transaction_date,
                ]
            );

            $item->update([
                'quantity' => $quantityAfter,
                'status' => $item->is_trackable
                    ? ($quantityAfter <= 0 ? 'out_of_stock' : ($item->status === 'out_of_stock' ? 'available' : $item->status))
                    : $item->status,
            ]);
        });

        $this->dispatch('close-modal', 'menu-item-adjustment-form');

        LivewireAlert::title('Adjustment Saved')
            ->text('Menu item adjustment saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $adjustment = MenuItemAdjustment::accessible()->findOrFail($id);

        abort_if($adjustment->sale_id, 403, 'Sale generated adjustments cannot be deleted here.');

        LivewireAlert::title('Delete Adjustment')
            ->text('Are you sure you want to delete this menu item adjustment?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data): void
    {
        $adjustment = MenuItemAdjustment::accessible()->findOrFail($data['id']);

        abort_if($adjustment->sale_id, 403, 'Sale generated adjustments cannot be deleted here.');

        $adjustment->delete();

        LivewireAlert::title('Adjustment Deleted')
            ->text('Menu item adjustment deleted successfully.')
            ->success()
            ->show();
    }

    protected function adjustmentsQuery(): Builder
    {
        return MenuItemAdjustment::query()
            ->accessible()
            ->when($this->search, fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query
                    ->whereHas('menuItem', fn (Builder $itemQuery) => $itemQuery
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%"))
                    ->orWhere('reference_no', 'like', "%{$this->search}%")
                    ->orWhere('reason', 'like', "%{$this->search}%")))
            ->when($this->filterBranch, fn (Builder $query) => $query->where('branch_id', $this->filterBranch))
            ->when($this->filterModule, fn (Builder $query) => $query->where('module_id', $this->filterModule))
            ->when($this->filterType, fn (Builder $query) => $query->where('type', $this->filterType));
    }

    protected function adjustmentTypes(): array
    {
        return [
            'purchase',
            'refund',
            'adjustment_increase',
            'adjustment_decrease',
            'manual_set',
            'transfer_in',
            'transfer_out',
            'wastage',
            'production',
            'opening_stock',
        ];
    }

    protected function validateQuantityDirection(): void
    {
        $before = (float) $this->quantity_before;
        $after = (float) $this->quantity_after;

        if (in_array($this->type, $this->increaseTypes(), true) && $after < $before) {
            $this->addError('quantity_after', 'Quantity after must be greater than or equal to quantity before for this adjustment type.');
        }

        if (in_array($this->type, $this->decreaseTypes(), true) && $after > $before) {
            $this->addError('quantity_after', 'Quantity after must be less than or equal to quantity before for this adjustment type.');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            throw ValidationException::withMessages($this->getErrorBag()->toArray());
        }
    }

    protected function increaseTypes(): array
    {
        return [
            'purchase',
            'refund',
            'adjustment_increase',
            'transfer_in',
            'production',
            'opening_stock',
        ];
    }

    protected function decreaseTypes(): array
    {
        return [
            'adjustment_decrease',
            'transfer_out',
            'wastage',
        ];
    }

    protected function generateReference(): string
    {
        return 'MIA-' . now()->format('YmdHis');
    }
}
