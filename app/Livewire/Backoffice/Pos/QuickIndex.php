<?php

namespace App\Livewire\Backoffice\Pos;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\MenuItem;
use App\Models\Sale;
use Illuminate\Support\Collection;

class QuickIndex extends Index
{
    public ?int $selectedCategoryId = null;

    public string $quickSearch = '';

    public float $tenderAmount = 0;

    public function updatedQuickSearch($value): void
    {
        if ($this->activeModuleId) {
            $this->menuSearch[$this->activeModuleId] = $value;
        }
    }

    public function selectCategory(?int $categoryId = null): void
    {
        $this->selectedCategoryId = $categoryId ? (int) $categoryId : null;
    }

    public function incrementCartItem(int $menuItemId, int $moduleId): void
    {
        $this->addToCart($menuItemId, $moduleId);
    }

    public function decrementCartItem(int $menuItemId, int $moduleId): void
    {
        $existing = collect($this->cart)->first(fn ($line) => (int) $line['menu_item_id'] === $menuItemId);
        if (! $existing) {
            return;
        }

        $currentQty = (int) $existing['qty'];
        if ($currentQty <= 1) {
            $this->removeCartItem($moduleId, $menuItemId);
        } else {
            $this->updateCartItem($moduleId, $menuItemId, $currentQty - 1);
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->autofillPaymentAmount(null, 0);
    }

    public function quickPay(string $method = 'cash'): void
    {
        $summary = $this->calculateOrderSummary();
        $total = (float) $summary['total'];

        $this->payment_method = $method;
        $this->splitPayments = [
            ['method' => $method, 'amount' => $total, 'transaction_reference' => null],
        ];
        $this->payment_amount = $total;

        $this->saveSale();
    }

    public function payWithTender(string $method = 'cash', ?float $customTender = null): void
    {
        $summary = $this->calculateOrderSummary();
        $total = (float) $summary['total'];
        $amount = $customTender !== null ? round((float) $customTender, 2) : ($this->tenderAmount > 0 ? $this->tenderAmount : $total);

        $effectivePaid = min($amount, $total);

        $this->payment_method = $method;
        $this->splitPayments = [
            ['method' => $method, 'amount' => $effectivePaid, 'transaction_reference' => null],
        ];
        $this->payment_amount = $effectivePaid;

        $this->saveSale();
    }

    protected function menuItemsByModule($branchId, $moduleIds)
    {
        $moduleIds = collect($moduleIds)->map(fn ($id) => (int) $id)->filter()->values();
        $activeModuleId = (int) $this->activeModuleId;

        if (! $branchId || $moduleIds->isEmpty() || ! $activeModuleId || ! $moduleIds->contains($activeModuleId)) {
            return collect();
        }

        $search = trim((string) data_get($this->menuSearch, $activeModuleId, ''));
        if (! $search && $this->quickSearch) {
            $search = trim($this->quickSearch);
        }

        return MenuItem::query()
            ->select([
                'id',
                'branch_id',
                'module_id',
                'category_id',
                'name',
                'description',
                'price',
                'quantity',
                'is_trackable',
                'image_url',
                'status',
            ])
            ->where('branch_id', $branchId)
            ->where('module_id', $activeModuleId)
            ->where('status', 'available')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($subQuery) => $subQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($this->selectedCategoryId, function ($query) {
                $query->where('category_id', $this->selectedCategoryId);
            })
            ->orderBy('name')
            ->get()
            ->groupBy('module_id');
    }

    public function render()
    {
        $branchId = session('branch_id');
        if ($branchId) {
            $this->authorizeBranch($branchId);
        }

        $modules = $this->getAccessibleModules($branchId);

        $moduleIds = $modules->pluck('id')->map(fn ($id) => (int) $id)->values();
        if ($moduleIds->isNotEmpty() && (! $this->activeModuleId || ! $moduleIds->contains((int) $this->activeModuleId))) {
            $this->activeModuleId = (int) $moduleIds->first();
        }

        $categories = $branchId && $this->activeModuleId
            ? Category::query()
                ->where('branch_id', $branchId)
                ->where(function ($query) {
                    $query->whereNull('module_id')->orWhere('module_id', $this->activeModuleId);
                })
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
            : collect();

        $menuItems = $this->menuItemsByModule($branchId, $moduleIds);
        $menuItemsUnified = $menuItems->flatten(1)->keyBy('id');
        $taxes = $this->taxesForBranch($branchId);
        $availableDiscounts = $this->discountsForBranch($branchId);

        $receiptSale = $this->receiptSaleId
            ? Sale::with(['branch', 'modules', 'customer', 'creator', 'items', 'payments.receiver'])
                ->accessible()
                ->find($this->receiptSaleId)
            : null;

        $cartLines = collect($this->cart);
        $filledCartModuleIds = $cartLines
            ->pluck('module_id')
            ->filter()
            ->unique()
            ->map(fn ($value) => (int) $value);

        $mixedCartModuleNames = $modules
            ->whereIn('id', $filledCartModuleIds)
            ->pluck('name');
        $orderSummary = $this->calculateOrderSummary($cartLines, $modules, $taxes, $availableDiscounts);

        return view('livewire.backoffice.pos.quick-index', [
            'modules' => $modules,
            'categories' => $categories,
            'menuItemsByModule' => $menuItems,
            'menuItemsUnified' => $menuItemsUnified,
            'taxes' => $taxes,
            'mixedCartModuleNames' => $mixedCartModuleNames,
            'mixedCartModuleCount' => $filledCartModuleIds->unique()->count(),
            'cartLines' => $cartLines,
            'orderSummary' => $orderSummary,
            'availableDiscounts' => $availableDiscounts,
            'customers' => $this->customerOptions(),
            'lastSales' => $this->showDailySales ? $this->dailySales($branchId, $moduleIds) : collect(),
            'receiptSale' => $receiptSale,
            'receiptTaxes' => $this->receiptTaxBreakdown($receiptSale),
            'recentPaymentSale' => $this->recentPaymentSaleId
                ? Sale::with(['customer', 'branch', 'modules'])->accessible()->find($this->recentPaymentSaleId)
                : null,
            'availableTables' => $branchId && $this->sale_type === 'dine_in'
                ? DiningTable::where('branch_id', $branchId)
                    ->where(function ($query) {
                        $query->where('status', 'available');

                        if ($this->sale_type === 'dine_in' && $this->table_id) {
                            $query->orWhere('id', $this->table_id);
                        }
                    })
                    ->orderBy('name')
                    ->get()
                : collect(),
            'receiptSettings' => $this->receiptSettings(),
            'branchId' => $branchId,
        ]);
    }
}
