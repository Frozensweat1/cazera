<?php

namespace App\Livewire\Backoffice\InventoryItemStocks;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryItem;
use App\Models\InventoryItemStock;
use App\Models\InventoryLocation;
use App\Models\InventoryStockAdjustment;
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
    public $filterItem = '';
    public $filterLocation = '';

    public $stockId;
    public $inventory_item_id;
    public $inventory_location_id;
    public $quantity_on_hand = 0;
    public $quantity_reserved = 0;
    public $reorder_level = 0;
    public $reorder_quantity = 0;
    public $notes;

    protected function rules()
    {
        return [
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'inventory_location_id' => 'nullable|exists:inventory_locations,id',
            'quantity_on_hand' => 'nullable|numeric|min:0',
            'quantity_reserved' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }

    public function render()
    {
        $branchIds = $this->accessibleBranches()->pluck('id');

        return view('livewire.backoffice.inventory-item-stocks.index', [
            'stocks' => InventoryItemStock::with(['inventoryItem', 'inventoryLocation'])
                ->where(function ($query) use ($branchIds) {
                    $query->whereHas('inventoryItem', fn($sub) => $sub->whereIn('branch_id', $branchIds))
                        ->orWhereHas('inventoryLocation', fn($sub) => $sub->whereIn('branch_id', $branchIds));
                })
                ->when($this->search, fn($query) => $query->where(fn ($query) => $query
                    ->whereHas('inventoryItem', fn($sub) => $sub->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('inventoryLocation', fn($sub) => $sub->where('name', 'like', "%{$this->search}%"))))
                ->when($this->filterBranch, fn($query) => $query->where(function ($query) {
                    $query->whereHas('inventoryItem', fn($sub) => $sub->where('branch_id', $this->filterBranch))
                        ->orWhereHas('inventoryLocation', fn($sub) => $sub->where('branch_id', $this->filterBranch));
                }))
                ->when($this->filterItem, fn($query) => $query->where('inventory_item_id', $this->filterItem))
                ->when($this->filterLocation, fn($query) => $query->where('inventory_location_id', $this->filterLocation))
                ->latest()
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
            'items' => InventoryItem::accessible()
                ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
                ->orderBy('name')
                ->get(),
            'locations' => InventoryLocation::accessible()
                ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
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
        $this->filterItem = '';
        $this->filterLocation = '';
        $this->resetPage();
    }

    public function updatedFilterItem(): void
    {
        $this->resetPage();
    }

    public function updatedFilterLocation(): void
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset([
            'stockId',
            'inventory_item_id',
            'inventory_location_id',
            'quantity_on_hand',
            'quantity_reserved',
            'reorder_level',
            'reorder_quantity',
            'notes',
        ]);

        $this->quantity_on_hand = 0;
        $this->quantity_reserved = 0;
        $this->reorder_level = 0;
        $this->reorder_quantity = 0;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'inventory-item-stock-form');
    }

    public function edit($id)
    {
        $stock = InventoryItemStock::findOrFail($id);

        $this->stockId = $stock->id;
        $this->inventory_item_id = $stock->inventory_item_id;
        $this->inventory_location_id = $stock->inventory_location_id;
        $this->quantity_on_hand = $stock->quantity_on_hand;
        $this->quantity_reserved = $stock->quantity_reserved;
        $this->reorder_level = $stock->reorder_level;
        $this->reorder_quantity = $stock->reorder_quantity;
        $this->notes = $stock->notes;

        $this->dispatch('open-modal', 'inventory-item-stock-form');
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $item = InventoryItem::accessible()->findOrFail($this->inventory_item_id);
            abort_if(! $item->is_trackable, 422, 'This item is not trackable and cannot have stock balances.');

            $before = 0;
            $isNew = ! $this->stockId;

            if ($this->stockId) {
                $stock = InventoryItemStock::query()
                    ->whereHas('inventoryItem', fn ($query) => $query->accessible())
                    ->findOrFail($this->stockId);
                $before = (float) $stock->quantity_on_hand;
                $stock->update($this->stockPayload());
            } else {
                $stock = InventoryItemStock::updateOrCreate([
                    'inventory_item_id' => $this->inventory_item_id,
                    'inventory_location_id' => $this->inventory_location_id,
                ], $this->stockPayload());
            }

            $after = (float) $this->quantity_on_hand;

            if ($before !== $after) {
                InventoryStockAdjustment::create([
                    'branch_id' => $item->branch_id,
                    'module_id' => $item->module_id,
                    'inventory_item_id' => $item->id,
                    'performed_by' => auth()->id(),
                    'type' => $isNew ? 'opening_stock' : 'manual_set',
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'change_qty' => $after - $before,
                    'reference_no' => 'STK-' . now()->format('YmdHis'),
                    'reason' => $isNew ? 'Initial stock record' : 'Stock record updated',
                    'notes' => $this->notes,
                    'transaction_date' => now(),
                ]);
            }

            $item->refreshAggregateQuantity();
        });

        $this->dispatch('close-modal', 'inventory-item-stock-form');

        LivewireAlert::title('Inventory Stock Saved')
            ->text('Inventory stock record saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    protected function stockPayload(): array
    {
        return [
            'inventory_item_id' => $this->inventory_item_id,
            'inventory_location_id' => $this->inventory_location_id,
            'quantity_on_hand' => $this->quantity_on_hand,
            'quantity_reserved' => $this->quantity_reserved,
            'reorder_level' => $this->reorder_level,
            'reorder_quantity' => $this->reorder_quantity,
            'notes' => $this->notes,
        ];
    }

    public function confirmDelete($id)
    {
        LivewireAlert::title('Delete Inventory Stock')
            ->text('Are you sure you want to delete this stock record?')
            ->asConfirm()
            ->onConfirm('delete', ['id' => $id])
            ->show();
    }

    public function delete($id)
    {
        $id = is_array($id) ? $id['id'] : $id;
        $stock = InventoryItemStock::query()
            ->whereHas('inventoryItem', fn ($query) => $query->accessible())
            ->findOrFail($id);
        $item = $stock->inventoryItem;

        $stock->delete();
        $item?->refreshAggregateQuantity();

        LivewireAlert::title('Inventory Stock Deleted')
            ->text('Inventory stock record deleted successfully.')
            ->success()
            ->show();
    }
}
