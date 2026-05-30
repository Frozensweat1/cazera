<?php

namespace App\Livewire\Backoffice\NetRevenue;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\CashRegisterTransaction;
use App\Models\DailyProductionCost;
use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Index extends Component
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

        $salesQuery = Sale::accessible()
            ->with(['branch', 'module'])
            ->where('status', '!=', 'cancelled')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('sale_date', [$startDate, $endDate]);

        $productionQuery = DailyProductionCost::accessible()
            ->with(['branch', 'module'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $expenseQuery = Expense::accessible()
            ->with(['branch', 'module', 'category'])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $refunds = CashRegisterTransaction::accessible()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->where('type', 'refund')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $salesCollected = (float) (clone $salesQuery)->sum('paid_amount');
        $grossSales = (float) (clone $salesQuery)->sum('total');
        $productionCosts = (float) (clone $productionQuery)->sum('amount');
        $expenses = (float) (clone $expenseQuery)->sum('amount');
        $netRevenue = $salesCollected - $productionCosts - $expenses;

        $branchBreakdown = (clone $salesQuery)
            ->selectRaw('branch_id, module_id, SUM(paid_amount) as paid_total, SUM(total) as gross_total')
            ->groupBy('branch_id', 'module_id')
            ->with(['branch', 'module'])
            ->get()
            ->map(function (Sale $sale) use ($startDate, $endDate) {
                $production = DailyProductionCost::accessible()
                    ->where('branch_id', $sale->branch_id)
                    ->where('module_id', $sale->module_id)
                    ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('amount');

                $expense = Expense::accessible()
                    ->where('branch_id', $sale->branch_id)
                    ->where('module_id', $sale->module_id)
                    ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('amount');

                $paid = (float) $sale->paid_total;

                return [
                    'branch' => $sale->branch?->name ?? 'Unknown',
                    'module' => $sale->module?->name ?? 'No module',
                    'paid' => $paid,
                    'production' => (float) $production,
                    'expenses' => (float) $expense,
                    'net' => $paid - (float) $production - (float) $expense,
                ];
            })
            ->sortByDesc('net')
            ->values();

        return view('livewire.backoffice.net-revenue.index', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'summary' => [
                'gross_sales' => $grossSales,
                'sales_collected' => $salesCollected,
                'refunds' => abs((float) $refunds),
                'production_costs' => $productionCosts,
                'expenses' => $expenses,
                'net_revenue' => $netRevenue,
                'margin' => $salesCollected > 0 ? round(($netRevenue / $salesCollected) * 100, 2) : 0,
            ],
            'branchBreakdown' => $branchBreakdown,
            'recentExpenses' => (clone $expenseQuery)->latest('expense_date')->take(6)->get(),
            'recentProductionCosts' => (clone $productionQuery)->latest('production_date')->take(6)->get(),
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
    }
}
