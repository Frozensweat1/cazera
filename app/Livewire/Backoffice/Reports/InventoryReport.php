<?php

namespace App\Livewire\Backoffice\Reports;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryItem;
use Livewire\Component;

class InventoryReport extends Component
{
    use HasBranchScope;

    public $filterBranch = '';
    public $filterModule = '';

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));

        $inventory = InventoryItem::accessible()
            ->when($branchId, fn($query) => $query->where('inventory_items.branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('inventory_items.module_id', $this->filterModule));

        $totalItems = (clone $inventory)->count();
        $activeItems = (clone $inventory)->where('is_active', true)->count();
        $inactiveItems = max(0, $totalItems - $activeItems);
        $inventoryValue = (clone $inventory)
            ->selectRaw('sum(quantity_on_hand * unit_cost) as value')
            ->value('value') ?: 0;
        $stockoutCount = (clone $inventory)->where('quantity_on_hand', '<=', 0)->count();
        $lowStockCount = (clone $inventory)->whereColumn('quantity_on_hand', '<=', 'reorder_level')->count();
        $reorderExposure = (clone $inventory)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->selectRaw('sum(GREATEST(reorder_level - quantity_on_hand, 0) * unit_cost) as value')
            ->value('value') ?: 0;

        $lowStockItems = (clone $inventory)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderByRaw('(reorder_level - quantity_on_hand) desc')
            ->take(8)
            ->get();

        $highValueItems = (clone $inventory)
            ->selectRaw('*, (quantity_on_hand * unit_cost) as stock_value')
            ->orderByDesc('stock_value')
            ->take(8)
            ->get();

        $topSuppliers = (clone $inventory)
            ->selectRaw('supplier_id, count(*) as item_count, sum(quantity_on_hand * unit_cost) as stock_value')
            ->with('supplier')
            ->groupBy('supplier_id')
            ->orderByDesc('stock_value')
            ->take(6)
            ->get();

        $valueByCategory = (clone $inventory)
            ->join('inventory_categories', 'inventory_items.category_id', '=', 'inventory_categories.id')
            ->selectRaw('inventory_categories.id as category_id, inventory_categories.name as category_name, sum(quantity_on_hand * unit_cost) as category_value')
            ->groupBy('inventory_categories.id', 'inventory_categories.name')
            ->orderByDesc('category_value')
            ->take(6)
            ->get();

        return view('livewire.backoffice.reports.inventory', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'branchId' => $branchId,
            'totalItems' => $totalItems,
            'activeItems' => $activeItems,
            'inactiveItems' => $inactiveItems,
            'inventoryValue' => $inventoryValue,
            'lowStockCount' => $lowStockCount,
            'stockoutCount' => $stockoutCount,
            'reorderExposure' => $reorderExposure,
            'lowStockItems' => $lowStockItems,
            'highValueItems' => $highValueItems,
            'topSuppliers' => $topSuppliers,
            'valueByCategory' => $valueByCategory,
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }
}
