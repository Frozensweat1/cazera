<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsIndex extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterType = '';
    public $filterDate = '';
    public $closingRegisterId;
    public $actual_balance = 0;
    public $closing_notes = '';

    public function mount(): void
    {
        $this->filterDate = now()->toDateString();
    }

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));
        $date = $this->filterDate ?: now()->toDateString();
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $baseTransactionsQuery = CashRegisterTransaction::with(['sale', 'branch', 'module', 'cashRegister', 'performer'])
            ->accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('transaction_date', [$startOfDay, $endOfDay])
            ->when($this->search, fn($query) => $query->where(function ($query) {
                $query->where('notes', 'like', "%{$this->search}%")
                    ->orWhereHas('sale', fn($q) => $q->where('sale_number', 'like', "%{$this->search}%"))
                    ->orWhereHas('cashRegister', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
            }));

        $transactionsQuery = (clone $baseTransactionsQuery)
            ->when($this->filterType, fn($query) => $query->where('type', $this->filterType));

        $registers = CashRegister::with([
            'branch',
            'module',
            'openedBy',
            'closedBy',
            'transactions' => fn($query) => $query->orderBy('transaction_date'),
        ])
            ->accessible()
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->where(function ($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('opened_at', [$startOfDay, $endOfDay])
                    ->orWhereBetween('closed_at', [$startOfDay, $endOfDay])
                    ->orWhereHas('transactions', fn($query) => $query->whereBetween('transaction_date', [$startOfDay, $endOfDay]));
            })
            ->latest('opened_at')
            ->get();

        $registers = $this->withRegisterHistoryTotals($registers);
        $closedRegisters = $registers->where('is_open', false);

        $expectedTotal = (float) $registers->sum('computed_expected_collection');
        $actualTotal = (float) $closedRegisters->sum('actual_balance');
        $differenceTotal = (float) $closedRegisters->sum('computed_difference');

        return view('livewire.backoffice.pos.transactions-index', [
            'transactions' => (clone $transactionsQuery)->latest('transaction_date')->paginate(15),
            'registers' => $registers,
            'summary' => [
                'registers' => $registers->count(),
                'open_registers' => $registers->where('is_open', true)->count(),
                'expected_total' => $expectedTotal,
                'actual_total' => $actualTotal,
                'difference_total' => $differenceTotal,
                'sale_collections' => (float) (clone $baseTransactionsQuery)->where('type', 'sale')->sum('amount'),
                'refunds' => abs((float) (clone $baseTransactionsQuery)->where('type', 'refund')->sum('amount')),
            ],
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'closingRegister' => $this->closingRegisterId
                ? $this->withRegisterHistoryTotals(collect([
                    CashRegister::with(['branch', 'module', 'openedBy', 'transactions'])->accessible()->find($this->closingRegisterId),
                ]))->first()
                : null,
        ]);
    }

    public function openCloseRegister($registerId): void
    {
        $register = CashRegister::accessible()
            ->where('is_open', true)
            ->findOrFail($registerId);

        $this->closingRegisterId = $register->id;
        $this->actual_balance = $this->expectedCollectionForRegister($register);
        $this->closing_notes = '';

        $this->dispatch('open-modal', 'cash-register-close-modal');
    }

    public function closeRegister(): void
    {
        $this->validate([
            'closingRegisterId' => 'required|exists:cash_registers,id',
            'actual_balance' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () {
            $register = CashRegister::accessible()
                ->where('is_open', true)
                ->lockForUpdate()
                ->findOrFail($this->closingRegisterId);

            $actual = round((float) $this->actual_balance, 2);
            $expected = $this->expectedCollectionForRegister($register);

            $register->update([
                'closed_by' => auth()->id(),
                'expected_balance' => $expected,
                'closing_balance' => $actual,
                'actual_balance' => $actual,
                'difference' => round($actual - $expected, 2),
                'is_open' => false,
                'closed_at' => now(),
                'notes' => $this->closing_notes,
            ]);
        });

        $this->reset(['closingRegisterId', 'closing_notes']);
        $this->actual_balance = 0;
        $this->dispatch('close-modal', 'cash-register-close-modal');

        LivewireAlert::title('Register Closed')
            ->text('Actual collection and variance have been recorded.')
            ->success()
            ->show();
    }

    protected function withRegisterHistoryTotals($registers)
    {
        return $registers
            ->filter()
            ->each(function (CashRegister $register) {
                $expected = $this->expectedCollectionForRegister($register);
                $actual = $register->is_open ? null : (float) $register->actual_balance;

                $register->setAttribute('computed_expected_collection', $expected);
                $register->setAttribute('computed_actual_collection', $actual);
                $register->setAttribute('computed_difference', $register->is_open ? null : round($actual - $expected, 2));
            });
    }

    protected function expectedCollectionForRegister(CashRegister $register): float
    {
        $transactions = $register->relationLoaded('transactions')
            ? $register->transactions
            : $register->transactions()->get();

        return round((float) $transactions
            ->filter(fn(CashRegisterTransaction $transaction) => CashRegister::transactionAffectsExpectedBalance($transaction->type))
            ->sum('amount'), 2);
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

    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}
