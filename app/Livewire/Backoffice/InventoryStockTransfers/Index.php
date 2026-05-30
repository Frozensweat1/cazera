<?php

namespace App\Livewire\Backoffice\InventoryStockTransfers;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryItemStock;
use App\Models\InventoryStockAdjustment;
use App\Models\InventoryStockTransfer;
use App\Models\Module;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterStatus = '';

    public $transferId;
    public $branch_id;
    public $module_id;
    public $inventory_item_id;
    public $destination_module_id;
    public $destination_category_id;
    public $destination_supplier_id;
    public $from_branch_id;
    public $to_branch_id;
    public $quantity = 0;
    public $reference_no;
    public $reason;
    public $notes;
    public $status = 'pending';
    public $transfer_date;

    protected function rules()
    {
        return [
            'branch_id' => 'nullable|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'destination_module_id' => 'nullable|exists:modules,id',
            'destination_category_id' => 'nullable|exists:inventory_categories,id',
            'destination_supplier_id' => 'nullable|exists:suppliers,id',
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'quantity' => 'required|numeric|min:0.01',
            'reference_no' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'transfer_date' => 'required|date',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.inventory-stock-transfers.index', [
            'transfers' => $this->transferQuery()->with([
                'branch',
                'module',
                'inventoryItem',
                'fromBranch',
                'toBranch',
                'destinationModule',
                'destinationCategory',
                'destinationSupplier',
                'performer',
            ])
                ->when(
                    $this->search,
                    fn($query) => $query->where(fn ($query) => $query
                        ->whereHas('inventoryItem', fn($sub) => $sub->where('name', 'like', "%{$this->search}%"))
                        ->orWhere('reference_no', 'like', "%{$this->search}%"))
                )
                ->when($this->filterBranch, fn($query) => $query->where(function ($query) {
                    $query->where('branch_id', $this->filterBranch)
                        ->orWhere('from_branch_id', $this->filterBranch)
                        ->orWhere('to_branch_id', $this->filterBranch);
                }))
                ->when($this->filterStatus, fn($query) => $query->where('status', $this->filterStatus))
                ->orderBy('transfer_date', 'desc')
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $this->branch_id ?: null),
            'destinationModules' => $this->to_branch_id
                ? $this->accessibleModules((int) $this->to_branch_id)
                : collect(),
            'destinationCategories' => InventoryCategory::accessible()
                ->where('is_active', true)
                ->when($this->to_branch_id, fn ($query) => $query->where('branch_id', $this->to_branch_id))
                ->when($this->destination_module_id, fn ($query) => $query->where('module_id', $this->destination_module_id))
                ->orderBy('name')
                ->get(),
            'destinationSuppliers' => Supplier::accessible()
                ->where('is_active', true)
                ->when($this->to_branch_id, fn ($query) => $query->where('branch_id', $this->to_branch_id))
                ->orderBy('name')
                ->get(),
            'items' => InventoryItem::accessible()
                ->when($this->from_branch_id, fn ($query) => $query->where('branch_id', $this->from_branch_id))
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
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset([
            'transferId',
            'branch_id',
            'module_id',
            'inventory_item_id',
            'destination_module_id',
            'destination_category_id',
            'destination_supplier_id',
            'from_branch_id',
            'to_branch_id',
            'quantity',
            'reference_no',
            'reason',
            'notes',
            'status',
        ]);

        $this->status = 'pending';
        $this->transfer_date = now()->format('Y-m-d');
    }

    public function updatedFromBranchId(): void
    {
        $this->inventory_item_id = null;
    }

    public function updatedToBranchId(): void
    {
        $this->destination_module_id = null;
        $this->destination_category_id = null;
        $this->destination_supplier_id = null;
    }

    public function updatedDestinationModuleId(): void
    {
        $this->destination_category_id = null;
    }

    public function updatedInventoryItemId(): void
    {
        if (! $this->inventory_item_id) {
            return;
        }

        $item = InventoryItem::accessible()->findOrFail($this->inventory_item_id);
        $this->from_branch_id = $item->branch_id;
        $this->branch_id = $item->branch_id;
        $this->module_id = $item->module_id;
    }

    public function create()
    {
        $this->resetForm();
        $this->transfer_date = now()->format('Y-m-d');
        $this->dispatch('open-modal', 'inventory-stock-transfer-form');
    }

    public function edit($id)
    {
        $transfer = $this->transferQuery()->findOrFail($id);
        abort_if($transfer->status !== 'pending', 422, 'Only pending transfers can be edited.');

        $this->transferId = $transfer->id;
        $this->branch_id = $transfer->branch_id;
        $this->module_id = $transfer->module_id;
        $this->inventory_item_id = $transfer->inventory_item_id;
        $this->destination_module_id = $transfer->destination_module_id;
        $this->destination_category_id = $transfer->destination_category_id;
        $this->destination_supplier_id = $transfer->destination_supplier_id;
        $this->from_branch_id = $transfer->from_branch_id;
        $this->to_branch_id = $transfer->to_branch_id;
        $this->quantity = $transfer->quantity;
        $this->reference_no = $transfer->reference_no;
        $this->reason = $transfer->reason;
        $this->notes = $transfer->notes;
        $this->status = $transfer->status;
        $this->transfer_date = $transfer->transfer_date->format('Y-m-d');

        $this->dispatch('open-modal', 'inventory-stock-transfer-form');
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $existing = $this->transferId
                ? $this->transferQuery()->findOrFail($this->transferId)
                : null;
            abort_if($existing && $existing->status !== 'pending', 422, 'Only pending transfers can be edited.');

            $sourceItem = InventoryItem::accessible()->findOrFail($this->inventory_item_id);
            abort_if(! $sourceItem->is_trackable, 422, 'This item is not trackable and cannot be transferred.');
            abort_if((int) $this->from_branch_id !== (int) $sourceItem->branch_id, 422, 'The selected item does not belong to the source branch.');

            $this->authorizeBranch($sourceItem->branch_id);
            $this->authorizeBranch($this->to_branch_id);
            $this->validateDestinationContext();

            $reference = $this->reference_no ?: 'TRF-' . now()->format('YmdHis');

            $transfer = InventoryStockTransfer::updateOrCreate([
                'id' => $this->transferId,
            ], [
                'branch_id' => $sourceItem->branch_id,
                'module_id' => $sourceItem->module_id,
                'inventory_item_id' => $sourceItem->id,
                'from_branch_id' => $sourceItem->branch_id,
                'to_branch_id' => $this->to_branch_id,
                'destination_module_id' => $this->destination_module_id,
                'destination_category_id' => $this->destination_category_id,
                'destination_supplier_id' => $this->destination_supplier_id,
                'performed_by' => auth()->id(),
                'quantity' => $this->quantity,
                'reference_no' => $reference,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'status' => $existing?->status ?? 'pending',
                'transfer_date' => $this->transfer_date,
            ]);
        });

        $this->dispatch('close-modal', 'inventory-stock-transfer-form');

        LivewireAlert::title('Stock Transfer Saved')
            ->text('Stock transfer saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function complete($data): void
    {
        $id = $this->transferActionId($data);

        DB::transaction(function () use ($id) {
            $transfer = $this->transferQuery()->lockForUpdate()->findOrFail($id);
            abort_if($transfer->status !== 'pending', 422, 'Only pending transfers can be completed.');
            abort_unless(auth()->user()?->canAccessBranch($transfer->to_branch_id), 403, 'Only the destination branch can complete this transfer.');

            $this->transferId = $transfer->id;
            $this->destination_module_id = $transfer->destination_module_id;
            $this->destination_category_id = $transfer->destination_category_id;
            $this->destination_supplier_id = $transfer->destination_supplier_id;
            $this->to_branch_id = $transfer->to_branch_id;
            $this->quantity = $transfer->quantity;
            $this->reason = $transfer->reason;
            $this->notes = $transfer->notes;
            $this->transfer_date = $transfer->transfer_date->format('Y-m-d');
            $this->status = 'completed';

            $this->validateDestinationContext(requireModuleAndCategory: true);

            $sourceItem = InventoryItem::query()->findOrFail($transfer->inventory_item_id);
            abort_if(! $sourceItem->is_trackable, 422, 'This item is not trackable and cannot be transferred.');

            $reference = $transfer->reference_no ?: 'TRF-' . now()->format('YmdHis');
            $this->completeTransfer($transfer, $sourceItem, $reference);
            $transfer->update([
                'status' => 'completed',
                'performed_by' => auth()->id(),
                'reference_no' => $reference,
            ]);
        });

        LivewireAlert::title('Transfer Completed')
            ->text('Stock has been received by the destination branch.')
            ->success()
            ->show();
    }

    public function cancel($data): void
    {
        $id = $this->transferActionId($data);
        $transfer = $this->transferQuery()->findOrFail($id);
        abort_if($transfer->status !== 'pending', 422, 'Only pending transfers can be cancelled.');
        abort_unless(auth()->user()?->canAccessBranch($transfer->from_branch_id), 403, 'Only the source branch can cancel this transfer.');

        $transfer->update([
            'status' => 'cancelled',
            'performed_by' => auth()->id(),
        ]);

        LivewireAlert::title('Transfer Cancelled')
            ->text('The pending stock transfer has been cancelled.')
            ->success()
            ->show();
    }

    public function confirmComplete($id): void
    {
        LivewireAlert::title('Complete Transfer')
            ->text('Receive this transfer into the destination branch and post stock movement?')
            ->asConfirm()
            ->onConfirm('complete', ['id' => $id])
            ->show();
    }

    public function confirmCancel($id): void
    {
        LivewireAlert::title('Cancel Transfer')
            ->text('Cancel this pending transfer request?')
            ->asConfirm()
            ->onConfirm('cancel', ['id' => $id])
            ->show();
    }

    protected function completeTransfer(InventoryStockTransfer $transfer, InventoryItem $sourceItem, string $reference): void
    {
        $quantity = (float) $this->quantity;

        $sourceBefore = $this->decrementSourceStock($sourceItem, $quantity);
        $sourceAfter = $sourceBefore - $quantity;

        InventoryStockAdjustment::create([
            'branch_id' => $sourceItem->branch_id,
            'module_id' => $sourceItem->module_id,
            'inventory_item_id' => $sourceItem->id,
            'performed_by' => auth()->id(),
            'type' => 'transfer_out',
            'quantity_before' => $sourceBefore,
            'quantity_after' => $sourceAfter,
            'change_qty' => -1 * $quantity,
            'reference_no' => $reference,
            'reason' => $this->reason ?: 'Stock transfer out',
            'notes' => $this->notes,
            'transaction_date' => $this->transfer_date,
        ]);

        $destinationItem = $this->resolveDestinationItem($sourceItem);
        $destinationStock = InventoryItemStock::receivingBalanceForItem($destinationItem->id);
        $destinationStock = InventoryItemStock::query()->lockForUpdate()->findOrFail($destinationStock->id);

        $destinationBefore = (float) $destinationStock->quantity_on_hand;
        $destinationAfter = $destinationBefore + $quantity;
        $destinationStock->update(['quantity_on_hand' => $destinationAfter]);

        InventoryStockAdjustment::create([
            'branch_id' => $destinationItem->branch_id,
            'module_id' => $destinationItem->module_id,
            'inventory_item_id' => $destinationItem->id,
            'performed_by' => auth()->id(),
            'type' => 'transfer_in',
            'quantity_before' => $destinationBefore,
            'quantity_after' => $destinationAfter,
            'change_qty' => $quantity,
            'reference_no' => $reference,
            'reason' => $this->reason ?: 'Stock transfer in',
            'notes' => $this->notes,
            'transaction_date' => $this->transfer_date,
        ]);

        $sourceItem->refreshAggregateQuantity();
        $destinationItem->refreshAggregateQuantity();
    }

    protected function resolveDestinationItem(InventoryItem $sourceItem): InventoryItem
    {
        $destinationItem = InventoryItem::query()
            ->where('branch_id', $this->to_branch_id)
            ->when($sourceItem->sku, fn ($query) => $query->where('sku', $sourceItem->sku))
            ->first();

        if (! $destinationItem && $sourceItem->slug) {
            $destinationItem = InventoryItem::query()
                ->where('branch_id', $this->to_branch_id)
                ->where('slug', $sourceItem->slug)
                ->first();
        }

        if ($destinationItem) {
            return tap($destinationItem)->update([
                'module_id' => $destinationItem->module_id ?: $this->destination_module_id,
                'category_id' => $destinationItem->category_id ?: $this->destination_category_id,
                'supplier_id' => $destinationItem->supplier_id ?: $this->destination_supplier_id,
            ]);
        }

        return InventoryItem::create([
            'branch_id' => $this->to_branch_id,
            'module_id' => $this->destination_module_id,
            'category_id' => $this->destination_category_id,
            'supplier_id' => $this->destination_supplier_id,
            'name' => $sourceItem->name,
            'slug' => $sourceItem->slug,
            'sku' => $sourceItem->sku,
            'barcode' => null,
            'description' => $sourceItem->description,
            'unit_cost' => $sourceItem->unit_cost,
            'unit_price' => $sourceItem->unit_price,
            'quantity_on_hand' => 0,
            'reorder_level' => $sourceItem->reorder_level,
            'reorder_quantity' => $sourceItem->reorder_quantity,
            'is_trackable' => $sourceItem->is_trackable,
            'is_active' => true,
            'settings' => $sourceItem->settings,
        ]);
    }

    protected function validateDestinationContext(bool $requireModuleAndCategory = false): void
    {
        if (! $this->destination_module_id && ! $requireModuleAndCategory) {
            return;
        }

        abort_if(! $this->destination_module_id, 422, 'Select a destination module before completing this transfer.');

        abort_unless(
            Module::query()
                ->where('id', $this->destination_module_id)
                ->where('branch_id', $this->to_branch_id)
                ->exists(),
            422,
            'The destination module must belong to the destination branch.'
        );

        if ($this->destination_category_id || $requireModuleAndCategory) {
            abort_unless(
                InventoryCategory::query()
                    ->where('id', $this->destination_category_id)
                    ->where('branch_id', $this->to_branch_id)
                    ->where('module_id', $this->destination_module_id)
                    ->exists(),
                422,
                'The destination category must belong to the selected destination module.'
            );
        }

        if ($this->destination_supplier_id) {
            abort_unless(
                Supplier::query()
                    ->where('id', $this->destination_supplier_id)
                    ->where('branch_id', $this->to_branch_id)
                    ->exists(),
                422,
                'The destination supplier must belong to the destination branch.'
            );
        }
    }

    public function confirmDelete($id)
    {
        LivewireAlert::title('Delete Stock Transfer')
            ->text('Are you sure you want to delete this pending stock transfer?')
            ->asConfirm()
            ->onConfirm('delete', ['id' => $id])
            ->show();
    }

    public function delete($id)
    {
        $id = $this->transferActionId($id);
        $transfer = $this->transferQuery()->findOrFail($id);
        abort_if($transfer->status === 'completed', 422, 'Completed transfers cannot be deleted because stock movement has already been posted.');
        abort_unless(auth()->user()?->canAccessBranch($transfer->from_branch_id), 403, 'Only the source branch can delete this transfer.');

        $transfer->delete();

        LivewireAlert::title('Stock Transfer Deleted')
            ->text('Stock transfer deleted successfully.')
            ->success()
            ->show();
    }

    protected function transferQuery()
    {
        $query = InventoryStockTransfer::query();
        $user = auth()->user();

        if (! $user || $user->isSuperAdmin()) {
            return $query;
        }

        $branchIds = $user->accessibleBranches()->pluck('branches.id');

        return $query->where(function ($query) use ($branchIds) {
            $query->whereIn('branch_id', $branchIds)
                ->orWhereIn('from_branch_id', $branchIds)
                ->orWhereIn('to_branch_id', $branchIds);
        });
    }

    protected function transferActionId($data): int
    {
        return (int) (is_array($data) ? $data['id'] : $data);
    }

    protected function decrementSourceStock(InventoryItem $sourceItem, float $quantity): float
    {
        $remaining = $quantity;
        $stocks = InventoryItemStock::query()
            ->where('inventory_item_id', $sourceItem->id)
            ->where('quantity_on_hand', '>', 0)
            ->orderByRaw('inventory_location_id IS NULL')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $sourceBefore = (float) $stocks->sum('quantity_on_hand');
        abort_if($sourceBefore < $quantity, 422, 'Insufficient source stock for this transfer.');

        $stocks->each(function (InventoryItemStock $stock) use (&$remaining) {
            if ($remaining <= 0) {
                return false;
            }

            $available = (float) $stock->quantity_on_hand;
            $deduct = min($available, $remaining);

            $stock->update([
                'quantity_on_hand' => $available - $deduct,
            ]);

            $remaining -= $deduct;
        });

        abort_if($remaining > 0, 422, 'Insufficient source stock for this transfer.');

        return $sourceBefore;
    }
}
