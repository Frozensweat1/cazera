<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class RefundsIndex extends Component
{
    use HasBranchScope;
    use WithPagination;

    public $search = '';

    public $filterBranch = '';

    public $filterModule = '';

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));

        return view('livewire.backoffice.pos.refunds-index', [
            'refunds' => Sale::with(['customer', 'modules', 'creator'])
                ->withSum([
                    'payments as refunded_amount' => fn ($query) => $query->where('status', 'refunded'),
                ], 'amount')
                ->accessible()
                ->whereHas('payments', fn ($query) => $query->where('status', 'refunded'))
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
        ]);
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
