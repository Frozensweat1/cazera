<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SplitPaymentsIndex extends Component
{
    use HasBranchScope;
    use WithPagination;

    public $search = '';

    public $filterBranch = '';

    public $filterModule = '';

    public $viewSaleId;

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));
        $viewSale = $this->viewSaleId
            ? Sale::with(['customer', 'branch', 'modules', 'creator', 'payments.receiver', 'payments.module'])
                ->accessible()
                ->whereHas('payments', fn ($query) => $query->where('status', 'completed'), '>', 1)
                ->find($this->viewSaleId)
            : null;

        return view('livewire.backoffice.pos.split-payments-index', [
            'salesWithSplits' => Sale::with(['customer', 'branch', 'modules', 'creator', 'payments'])
                ->accessible()
                ->whereHas('payments', fn ($query) => $query->where('status', 'completed'), '>', 1)
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->forModule($this->filterModule)
                ->when($this->search, fn ($query) => $query->where(function ($query) {
                    $query->where('sale_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
                }))
                ->latest('sale_date')
                ->paginate(15),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'viewSale' => $viewSale,
        ]);
    }

    public function viewPayments($saleId): void
    {
        $sale = Sale::accessible()
            ->whereHas('payments', fn ($query) => $query->where('status', 'completed'), '>', 1)
            ->findOrFail($saleId);

        $this->viewSaleId = $sale->id;
        $this->dispatch('open-modal', 'split-payments-view-modal');
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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}
