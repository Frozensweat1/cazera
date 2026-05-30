<?php

namespace App\Livewire\Backoffice\InventoryStockMovements;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryStockAdjustment;
use App\Models\InventoryStockTransfer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterType = 'all';

    public function render()
    {
        $branchId = $this->filterBranch;
        $searchTerm = trim($this->search);

        $adjustments = collect();
        $transfers = collect();

        if ($this->filterType !== 'transfer') {
            $adjustments = InventoryStockAdjustment::with(['inventoryItem', 'branch', 'performer'])
                ->accessible()
                ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                ->when($searchTerm, fn($query) => $query->where(fn ($query) => $query
                    ->whereHas('inventoryItem', fn($sub) => $sub->where('name', 'like', "%{$searchTerm}%"))
                    ->orWhere('reference_no', 'like', "%{$searchTerm}%")
                    ->orWhere('reason', 'like', "%{$searchTerm}%")))
                ->get()
                ->map(function ($adjustment) {
                    return (object) [
                        'id' => "adjustment-{$adjustment->id}",
                        'movement_type' => 'Adjustment',
                        'item_name' => $adjustment->inventoryItem?->name,
                        'source' => ucfirst(str_replace('_', ' ', $adjustment->type)),
                        'destination' => $adjustment->branch?->name,
                        'quantity' => $adjustment->change_qty,
                        'status' => '',
                        'date' => $adjustment->transaction_date,
                        'reference' => $adjustment->reference_no,
                        'notes' => $adjustment->notes,
                        'performed_by' => $adjustment->performer?->name,
                        'branch_name' => $adjustment->branch?->name,
                    ];
                });
        }

        if ($this->filterType !== 'adjustment') {
            $transfers = InventoryStockTransfer::with(['inventoryItem', 'fromBranch', 'toBranch', 'branch', 'performer'])
                ->accessible()
                ->when($branchId, fn($query) => $query->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                        ->orWhere('from_branch_id', $branchId)
                        ->orWhere('to_branch_id', $branchId);
                }))
                ->when($searchTerm, fn($query) => $query->where(fn ($query) => $query
                    ->whereHas('inventoryItem', fn($sub) => $sub->where('name', 'like', "%{$searchTerm}%"))
                    ->orWhere('reference_no', 'like', "%{$searchTerm}%")
                    ->orWhere('reason', 'like', "%{$searchTerm}%")))
                ->get()
                ->map(function ($transfer) {
                    return (object) [
                        'id' => "transfer-{$transfer->id}",
                        'movement_type' => 'Transfer',
                        'item_name' => $transfer->inventoryItem?->name,
                        'source' => $transfer->fromBranch?->name,
                        'destination' => $transfer->toBranch?->name,
                        'quantity' => $transfer->quantity,
                        'status' => ucfirst($transfer->status),
                        'date' => $transfer->transfer_date,
                        'reference' => $transfer->reference_no,
                        'notes' => $transfer->notes,
                        'performed_by' => $transfer->performer?->name,
                        'branch_name' => $transfer->branch?->name,
                    ];
                });
        }

        $movements = collect();

        if ($this->filterType !== 'transfer') {
            $movements = $movements->concat($adjustments);
        }

        if ($this->filterType !== 'adjustment') {
            $movements = $movements->concat($transfers);
        }

        $movements = $movements->sortByDesc('date')->values();

        $page = Paginator::resolveCurrentPage('page');
        $perPage = 10;
        $paginatedMovements = new LengthAwarePaginator(
            $movements->forPage($page, $perPage),
            $movements->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        return view('livewire.backoffice.inventory-stock-movements.index', [
            'movements' => $paginatedMovements,
            'branches' => $this->accessibleBranches(),
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

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }
}
