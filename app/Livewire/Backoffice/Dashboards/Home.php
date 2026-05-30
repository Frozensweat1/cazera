<?php

namespace App\Livewire\Backoffice\Dashboards;

use App\Livewire\Backoffice\Dashboards\Concerns\HasDashboardFilters;
use App\Livewire\Concerns\HasBranchScope;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Home extends Component
{
    use HasBranchScope;
    use HasDashboardFilters;

    public float $grossSales = 0.0;
    public float $collectedSales = 0.0;
    public float $outstandingDebt = 0.0;
    public float $averageTicket = 0.0;
    public int $totalOrders = 0;
    public int $completedOrders = 0;
    public int $openOrders = 0;
    public int $refundCount = 0;
    public array $salesLabels = [];
    public array $salesSeries = [];
    public array $ordersSeries = [];
    public array $categoryLabels = [];
    public array $categorySeries = [];
    public array $paymentLabels = [];
    public array $paymentSeries = [];
    public array $topItems = [];
    public array $recentSales = [];

    public function mount(): void
    {
        $this->mountDashboardFilters(0);
    }

    public function render()
    {
        $this->loadMetrics();

        return view('livewire.backoffice.dashboards.home', [
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->dashboardBranchId()),
        ]);
    }

    protected function loadMetrics(): void
    {
        [$from, $to] = $this->dashboardDateRange();

        $salesQuery = $this->applyDateRange(
            $this->applyDashboardScope(Sale::query()),
            'sale_date'
        )->whereNotIn('status', ['cancelled', 'refunded']);

        $this->grossSales = (float) (clone $salesQuery)->sum('total');
        $this->collectedSales = (float) (clone $salesQuery)->sum('paid_amount');
        $this->outstandingDebt = (float) (clone $salesQuery)->sum('remaining_balance');
        $this->totalOrders = (int) (clone $salesQuery)->count();
        $this->completedOrders = (int) (clone $salesQuery)->whereIn('status', ['completed', 'paid'])->count();
        $this->openOrders = (int) (clone $salesQuery)->whereNotIn('status', ['completed', 'paid', 'cancelled', 'refunded'])->count();
        $this->averageTicket = $this->totalOrders > 0 ? round($this->grossSales / $this->totalOrders, 2) : 0.0;
        $this->refundCount = (int) $this->applyDateRange(
            $this->applyDashboardScope(Sale::query()),
            'sale_date'
        )->where('status', 'refunded')->count();

        $days = collect(CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay()));
        $salesByDay = (clone $salesQuery)
            ->selectRaw('DATE(sale_date) as date, SUM(paid_amount) as collected, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $this->salesLabels = $days->map(fn ($day) => $day->format('M d'))->toArray();
        $this->salesSeries = $days->map(fn ($day) => round((float) ($salesByDay->get($day->toDateString())->collected ?? 0), 2))->toArray();
        $this->ordersSeries = $days->map(fn ($day) => (int) ($salesByDay->get($day->toDateString())->orders ?? 0))->toArray();

        $categoryData = $this->applyDashboardScope(SaleItem::query(), 'sale_items.branch_id', 'sale_items.module_id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('menu_items', 'sale_items.menu_item_id', '=', 'menu_items.id')
            ->leftJoin('categories', 'menu_items.category_id', '=', 'categories.id')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->whereNotIn('sales.status', ['cancelled', 'refunded'])
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category_name, SUM(sale_items.total) as total")
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $this->categoryLabels = $categoryData->pluck('category_name')->toArray();
        $this->categorySeries = $categoryData->map(fn ($item) => round((float) $item->total, 2))->toArray();

        $paymentData = $this->applyDateRange(
            $this->applyDashboardScope(Payment::query()),
            'paid_at'
        )
            ->where('status', 'completed')
            ->selectRaw("COALESCE(NULLIF(method, ''), 'Other') as method_name, SUM(amount) as total")
            ->groupBy('method_name')
            ->orderByDesc('total')
            ->get();

        $this->paymentLabels = $paymentData->pluck('method_name')->map(fn ($method) => str($method)->title()->toString())->toArray();
        $this->paymentSeries = $paymentData->map(fn ($item) => round((float) $item->total, 2))->toArray();

        $this->topItems = $this->applyDashboardScope(SaleItem::query(), 'sale_items.branch_id', 'sale_items.module_id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$from, $to])
            ->whereNotIn('sales.status', ['cancelled', 'refunded'])
            ->select('sale_items.item_name', DB::raw('SUM(sale_items.qty) as quantity'), DB::raw('SUM(sale_items.total) as revenue'))
            ->groupBy('sale_items.item_name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->item_name,
                'quantity' => (float) $item->quantity,
                'revenue' => (float) $item->revenue,
            ])
            ->toArray();

        $this->recentSales = (clone $salesQuery)
            ->with(['customer', 'branch', 'module'])
            ->latest('sale_date')
            ->limit(8)
            ->get()
            ->map(fn (Sale $sale) => [
                'sale_number' => $sale->sale_number,
                'customer' => $sale->customer?->name ?: 'Walk-in customer',
                'branch' => $sale->branch?->name ?: 'Branch',
                'module' => $sale->module?->name ?: 'Module',
                'status' => $sale->status,
                'total' => (float) $sale->total,
                'paid' => (float) $sale->paid_amount,
                'time' => $sale->sale_date?->diffForHumans(),
            ])
            ->toArray();
    }
}
