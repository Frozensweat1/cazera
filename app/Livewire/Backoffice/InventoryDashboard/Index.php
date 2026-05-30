<?php

namespace App\Livewire\Backoffice\InventoryDashboard;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryStockAdjustment;
use App\Models\InventoryStockTransfer;
use Livewire\Component;

class Index extends Component
{
    use HasBranchScope;

    public function render()
    {
        $branchId = session('branch_id');

        return view('livewire.backoffice.inventory-dashboard.index', [
            'totalItems' => InventoryItem::accessible()->when($branchId, fn($query) => $query->where('branch_id', $branchId))->count(),
            'activeItems' => InventoryItem::accessible()->when($branchId, fn($query) => $query->where('branch_id', $branchId))->where('is_active', true)->count(),
            'totalLocations' => InventoryLocation::accessible()->when($branchId, fn($query) => $query->where('branch_id', $branchId))->count(),
            'activeLocations' => InventoryLocation::accessible()->when($branchId, fn($query) => $query->where('branch_id', $branchId))->where('is_active', true)->count(),
            'lowStockItems' => InventoryItem::accessible()->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                ->where('is_active', true)
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->orderBy('quantity_on_hand')
                ->take(5)
                ->get(),
            'recentAdjustments' => InventoryStockAdjustment::accessible()->with(['inventoryItem'])
                ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                ->latest('transaction_date')
                ->take(5)
                ->get(),
            'recentTransfers' => InventoryStockTransfer::accessible()->with(['inventoryItem', 'fromBranch', 'toBranch'])
                ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                ->latest('transfer_date')
                ->take(5)
                ->get(),
        ]);
    }
}
