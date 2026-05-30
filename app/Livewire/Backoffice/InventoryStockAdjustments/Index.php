<?php

namespace App\Livewire\Backoffice\InventoryStockAdjustments;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryItem;
use App\Models\InventoryItemStock;
use App\Models\InventoryStockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
    public $filterType = '';

    public $adjustmentId;
    public $branch_id;
    public $module_id;
    public $inventory_item_id;
    public $performed_by;
    public $type = 'adjustment_increase';
    public $quantity_before = 0;
    public $quantity_after = 0;
    public $reference_no;
    public $reason;
    public $notes;
    public $transaction_date;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'performed_by' => 'nullable|exists:users,id',
            'type' => ['required', Rule::in([
                'purchase',
                'sale',
                'adjustment_increase',
                'adjustment_decrease',
                'transfer_in',
                'transfer_out',
                'wastage',
                'manual_set',
                'opening_stock',
            ])],
            'quantity_before' => 'required|numeric|min:0',
            'quantity_after' => 'required|numeric|min:0',
            'reference_no' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'transaction_date' => 'required|date',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.inventory-stock-adjustments.index', [
            'adjustments' => InventoryStockAdjustment::with(['branch', 'module', 'inventoryItem', 'performer'])
                ->accessible()
                ->when(
                    $this->search,
                    fn($query) => $query->where(fn ($query) => $query
                        ->whereHas('inventoryItem', fn($sub) => $sub->where('name', 'like', "%{$this->search}%"))
                        ->orWhere('reference_no', 'like', "%{$this->search}%"))
                )
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterType, fn($query) => $query->where('type', $this->filterType))
                ->orderBy('transaction_date', 'desc')
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),
            'formModules' => $this->branch_id
                ? $this->accessibleModules((int) $this->branch_id)
                : collect(),
            'items' => InventoryItem::accessible()->with('branch')
                ->when($this->branch_id, fn($query) => $query->where('branch_id', $this->branch_id))
                ->when($this->module_id, fn($query) => $query->where('module_id', $this->module_id))
                ->orderBy('name')
                ->get(),
            'users' => User::orderBy('name')->get(),
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
        $this->inventory_item_id = null;
        $this->quantity_before = 0;
        $this->quantity_after = 0;
    }

    public function updatedModuleId(): void
    {
        $this->inventory_item_id = null;
        $this->quantity_before = 0;
        $this->quantity_after = 0;
    }

    public function updatedInventoryItemId(): void
    {
        if (! $this->inventory_item_id) {
            $this->quantity_before = 0;
            $this->quantity_after = 0;
            return;
        }

        $item = InventoryItem::accessible()->findOrFail($this->inventory_item_id);
        $this->branch_id = $item->branch_id;
        $this->module_id = $item->module_id;
        $this->quantity_before = $item->quantity_on_hand ?? 0;
        $this->quantity_after = $item->quantity_on_hand ?? 0;
    }

    public function updatedQuantityAfter()
    {
        $this->computeChangeQty();
    }

    public function updatedQuantityBefore()
    {
        $this->computeChangeQty();
    }

    protected function computeChangeQty()
    {
        $this->dispatch('inventory-adjustment-updated');
    }

    public function resetForm()
    {
        $this->reset([
            'adjustmentId',
            'branch_id',
            'module_id',
            'inventory_item_id',
            'performed_by',
            'type',
            'quantity_before',
            'quantity_after',
            'reference_no',
            'reason',
            'notes',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->performed_by = auth()->id();
        $this->transaction_date = now()->format('Y-m-d');
    }

    public function create()
    {
        $this->resetForm();
        $this->transaction_date = now()->format('Y-m-d');
        $this->dispatch('open-modal', 'inventory-stock-adjustment-form');
    }

    public function edit($id)
    {
        $adjustment = InventoryStockAdjustment::accessible()->findOrFail($id);

        $this->adjustmentId = $adjustment->id;
        $this->branch_id = $adjustment->branch_id;
        $this->module_id = $adjustment->module_id;
        $this->inventory_item_id = $adjustment->inventory_item_id;
        $this->performed_by = $adjustment->performed_by;
        $this->type = $adjustment->type;
        $this->quantity_before = $adjustment->quantity_before;
        $this->quantity_after = $adjustment->quantity_after;
        $this->reference_no = $adjustment->reference_no;
        $this->reason = $adjustment->reason;
        $this->notes = $adjustment->notes;
        $this->transaction_date = $adjustment->transaction_date->format('Y-m-d');

        $this->dispatch('open-modal', 'inventory-stock-adjustment-form');
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $item = InventoryItem::accessible()->findOrFail($this->inventory_item_id);
            abort_if(! $item->is_trackable, 422, 'This item is not trackable and cannot have stock adjustments.');

            $this->authorizeBranch($item->branch_id);
            $this->authorizeModule($item->module_id, $item->branch_id);

            $stock = InventoryItemStock::unassignedForItem($item->id, (float) ($item->quantity_on_hand ?? 0));

            $before = (float) $stock->quantity_on_hand;
            $after = (float) $this->quantity_after;

            InventoryStockAdjustment::updateOrCreate([
                'id' => $this->adjustmentId,
            ], [
                'branch_id' => $item->branch_id,
                'module_id' => $item->module_id,
                'inventory_item_id' => $item->id,
                'performed_by' => $this->performed_by ?: auth()->id(),
                'type' => $this->type,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_qty' => $after - $before,
                'reference_no' => $this->reference_no ?: 'ADJ-' . now()->format('YmdHis'),
                'reason' => $this->reason,
                'notes' => $this->notes,
                'transaction_date' => $this->transaction_date,
            ]);

            $stock->update([
                'quantity_on_hand' => $after,
            ]);

            $item->refreshAggregateQuantity();
        });

        $this->dispatch('close-modal', 'inventory-stock-adjustment-form');

        LivewireAlert::title('Stock Adjustment Saved')
            ->text('Stock adjustment saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        LivewireAlert::title('Delete Stock Adjustment')
            ->text('Are you sure you want to delete this stock adjustment?')
            ->asConfirm()
            ->onConfirm('delete', ['id' => $id])
            ->show();
    }

    public function delete($id)
    {
        $id = is_array($id) ? $id['id'] : $id;
        InventoryStockAdjustment::accessible()->findOrFail($id)->delete();

        LivewireAlert::title('Stock Adjustment Deleted')
            ->text('Stock adjustment deleted successfully.')
            ->success()
            ->show();
    }
}
