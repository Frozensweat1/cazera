<?php

namespace App\Livewire\Backoffice\Reports;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use Illuminate\Support\Carbon;
use Livewire\Component;

class CashRegisterReport extends Component
{
    use HasBranchScope;

    public $filterBranch = '';
    public $filterModule = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));
        $startDate = Carbon::parse($this->dateFrom ?: now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($this->dateTo ?: now()->toDateString())->endOfDay();

        $transactions = CashRegisterTransaction::accessible()
            ->with(['cashRegister', 'branch', 'module', 'sale', 'performer'])
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        $totalTransactions = (clone $transactions)->count();
        $transactionVolume = (clone $transactions)->sum('amount');
        $averageTransaction = $totalTransactions ? round($transactionVolume / $totalTransactions, 2) : 0;
        $saleCollections = (clone $transactions)->where('type', 'sale')->sum('amount');
        $refunds = abs((float) (clone $transactions)->where('type', 'refund')->sum('amount'));
        $cashIn = (clone $transactions)->where('type', 'cash_in')->sum('amount');
        $cashOut = abs((float) (clone $transactions)->where('type', 'cash_out')->sum('amount'));

        $transactionTypes = (clone $transactions)
            ->selectRaw('type, count(*) as count, sum(amount) as total_amount')
            ->groupBy('type')
            ->orderByDesc('total_amount')
            ->get();

        $registerBalances = CashRegister::accessible()
            ->with(['branch', 'module', 'openedBy', 'closedBy'])
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('opened_at', [$startDate, $endDate])
                    ->orWhereBetween('closed_at', [$startDate, $endDate])
                    ->orWhereHas('transactions', fn($query) => $query->whereBetween('transaction_date', [$startDate, $endDate]));
            })
            ->orderBy('name')
            ->get();

        $openRegisters = $registerBalances->where('is_open', true)->count();
        $closedRegisters = $registerBalances->where('is_open', false);
        $expectedTotal = (float) $registerBalances->sum('expected_balance');
        $actualTotal = (float) $closedRegisters->sum('actual_balance');
        $differenceTotal = (float) $closedRegisters->sum('difference');

        $recentTransactions = (clone $transactions)
            ->latest('transaction_date')
            ->take(8)
            ->get();

        return view('livewire.backoffice.reports.cash-register', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'branchId' => $branchId,
            'totalTransactions' => $totalTransactions,
            'totalVolume' => $transactionVolume,
            'averageTransaction' => $averageTransaction,
            'openRegisters' => $openRegisters,
            'saleCollections' => $saleCollections,
            'refunds' => $refunds,
            'cashIn' => $cashIn,
            'cashOut' => $cashOut,
            'expectedTotal' => $expectedTotal,
            'actualTotal' => $actualTotal,
            'differenceTotal' => $differenceTotal,
            'transactionTypes' => $transactionTypes,
            'registerBalances' => $registerBalances,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }
}
