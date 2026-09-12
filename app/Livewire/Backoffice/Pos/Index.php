<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Discount;
use App\Models\MenuItem;
use App\Models\MenuItemAdjustment;
use App\Models\Module;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tax;
use App\Support\PosCache;
use App\Support\SalePaymentRecorder;
use App\Support\SaleTableRelease;
use App\Support\WebsiteContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Throwable;

class Index extends Component
{
    use HasBranchScope;

    public $customer_id;

    public $customer_search = '';

    public $new_customer_name;

    public $new_customer_email;

    public $new_customer_phone;

    public $new_customer_address;

    public $sale_type = 'dine_in';

    public $payment_method = 'cash';

    public $payment_amount = 0;

    public $splitPayments = [];

    public $menuSearch = [];

    public $discount = 0;

    public $discount_id;

    public $notes;

    public $status = 'pending';

    public $notifyKitchen = false;

    public $table_id;

    public $cart = [];

    public $receiptSaleId;

    public $recentPaymentSaleId;

    public $recent_payment_method = 'cash';

    public $recent_payment_amount = 0;

    public $recent_payment_reference = '';

    public $editingSaleId;

    public $editingSaleNumber;

    public $editingOriginalDiscount = 0;

    public $activeModuleId;

    public bool $showDailySales = false;

    protected array $cashPaymentMethods = ['cash', 'mobile_money', 'card', 'bank_transfer', 'wallet'];

    public function mount()
    {
        $this->resetSaleForm();
    }

    protected function resetSaleForm(): void
    {
        $this->customer_id = null;
        $this->customer_search = '';
        $this->sale_type = 'dine_in';
        $this->payment_method = 'cash';
        $this->payment_amount = 0;
        $this->splitPayments = [
            ['method' => 'cash', 'amount' => 0, 'transaction_reference' => null],
        ];
        $this->discount = 0;
        $this->discount_id = null;
        $this->notes = '';
        $this->notifyKitchen = false;
        $this->table_id = null;
        $this->cart = [];
        $this->editingSaleId = null;
        $this->editingSaleNumber = null;
        $this->editingOriginalDiscount = 0;
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

        return view('livewire.backoffice.pos.index', [
            'modules' => $modules,
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

    public function getAccessibleModules($branchId)
    {
        if (! $branchId) {
            return collect();
        }

        return Cache::remember(
            PosCache::accessibleModulesKey((int) auth()->id(), (int) $branchId),
            now()->addMinute(),
            fn () => $this->accessibleModules($branchId, 'pos')
        );
    }

    protected function menuItemsByModule($branchId, $moduleIds)
    {
        $moduleIds = collect($moduleIds)->map(fn ($id) => (int) $id)->filter()->values();
        $activeModuleId = (int) $this->activeModuleId;

        if (! $branchId || $moduleIds->isEmpty() || ! $activeModuleId || ! $moduleIds->contains($activeModuleId)) {
            return collect();
        }

        $search = trim((string) data_get($this->menuSearch, $activeModuleId, ''));

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
            ->orderBy('name')
            ->get()
            ->groupBy('module_id');
    }

    protected function taxesForBranch($branchId)
    {
        if (! $branchId) {
            return collect();
        }

        return Cache::remember(
            PosCache::taxesKey((int) $branchId),
            now()->addMinutes(5),
            fn () => Tax::query()
                ->where('branch_id', $branchId)
                ->available()
                ->orderBy('name')
                ->get()
        );
    }

    protected function discountsForBranch($branchId)
    {
        if (! $branchId) {
            return collect();
        }

        return Cache::remember(
            PosCache::discountsKey((int) $branchId),
            now()->addMinutes(5),
            fn () => Discount::query()
                ->where('branch_id', $branchId)
                ->available()
                ->orderBy('name')
                ->get()
        );
    }

    public function setActiveModule(int $moduleId): void
    {
        $branchId = session('branch_id');
        $this->authorizeModule($moduleId, $branchId);
        $this->activeModuleId = $moduleId;
    }

    public function loadDailySales(): void
    {
        $this->showDailySales = true;
    }

    protected function dailySales($branchId, $moduleIds)
    {
        if (! $branchId) {
            return collect();
        }

        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $canSeeAllTodaySales = auth()->user()?->isSuperAdmin() || auth()->user()?->isBranchManager();

        return Sale::with(['customer:id,name', 'modules', 'latestPayment.receiver:id,name', 'table:id,name,status'])
            ->where('branch_id', $branchId)
            ->where('status', '!=', 'refunded')
            ->whereBetween('sale_date', [$todayStart, $todayEnd])
            ->forModules($moduleIds)
            ->when(! $canSeeAllTodaySales, fn ($query) => $query->where('created_by', auth()->id()))
            ->latest('sale_date')
            ->limit(30)
            ->get();
    }

    protected function customerOptions()
    {
        $search = trim((string) $this->customer_search);

        if ($search === '' || $this->customer_id) {
            return collect();
        }

        return Customer::query()
            ->select(['id', 'name', 'phone', 'email'])
            ->where('branch_id', session('branch_id'))
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    public function addToCart($menuItemId, $moduleId)
    {
        try {
            $moduleId = (int) $moduleId;
            $branchId = session('branch_id');
            $this->authorizeModule($moduleId, $branchId);

            // allow adding items from any module accessible to the user
            $item = MenuItem::where('branch_id', $branchId)
                ->select(['id', 'branch_id', 'module_id', 'name', 'price', 'quantity', 'is_trackable', 'status'])
                ->with('module:id,name')
                ->where('status', 'available')
                ->findOrFail($menuItemId);

            $moduleId = (int) ($item->module_id ?? $moduleId);

            if ($item->is_trackable && (float) $item->quantity <= 0) {
                throw ValidationException::withMessages([
                    'cart' => "{$item->name} is out of stock.",
                ]);
            }

            $this->authorizeModule($item->module_id, $branchId);
            abort_unless((int) $item->module_id === $moduleId, 403);

            $cart = $this->cart;
            $existingIndex = collect($cart)->search(fn ($line) => (int) $line['menu_item_id'] === (int) $item->id);

            if ($existingIndex !== false) {
                foreach ($cart as $index => $line) {
                    if ($index === $existingIndex) {
                        $requestedQty = (int) $line['qty'] + 1;

                        if ($item->is_trackable && $requestedQty > (float) $item->quantity) {
                            throw ValidationException::withMessages([
                                'cart' => "{$item->name} has only ".number_format((float) $item->quantity, 0).' available.',
                            ]);
                        }

                        $line['qty'] = $requestedQty;
                        $line['subtotal'] = $line['qty'] * $line['unit_price'];
                        $cart[$index] = $line;
                        break;
                    }
                }
            } else {
                $cart[] = [
                    'menu_item_id' => $item->id,
                    'module_id' => $item->module_id,
                    'module_name' => $item->module?->name,
                    'item_name' => $item->name,
                    'qty' => 1,
                    'unit_price' => (float) $item->price,
                    'tax' => 0,
                    'discount' => 0,
                    'subtotal' => (float) $item->price,
                ];
            }

            $this->cart = array_values($cart);

            $this->autofillPaymentAmount(null, 0);
        } catch (ValidationException $e) {
            $this->showCartError($this->firstValidationMessage($e));
        } catch (Throwable $e) {
            Log::error('PosIndex::addToCart failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'menu_item_id' => $menuItemId]);
            LivewireAlert::title('Unable to Add Item')
                ->text('Please refresh and try again.')
                ->error()
                ->show();
        }
    }

    public function updateCartItem($moduleId, $menuItemId, $qty)
    {
        try {
            $moduleId = (int) $moduleId;
            $branchId = session('branch_id');
            $this->authorizeModule($moduleId, $branchId);
            $qty = max(1, intval($qty));
            $item = MenuItem::where('branch_id', $branchId)
                ->select(['id', 'branch_id', 'module_id', 'name', 'price', 'quantity', 'is_trackable', 'status'])
                ->where('module_id', $moduleId)
                ->where('status', 'available')
                ->findOrFail($menuItemId);

            if ($item->is_trackable && $qty > (float) $item->quantity) {
                throw ValidationException::withMessages([
                    'cart' => "{$item->name} has only ".number_format((float) $item->quantity, 0).' available.',
                ]);
            }

            $cart = $this->cart;
            foreach ($cart as $index => $line) {
                if ((int) $line['menu_item_id'] === (int) $menuItemId) {
                    $line['qty'] = $qty;
                    $line['subtotal'] = $qty * $line['unit_price'];
                    $cart[$index] = $line;
                    break;
                }
            }

            $this->cart = array_values($cart);

            $this->autofillPaymentAmount(null, 0);
        } catch (ValidationException $e) {
            $this->showCartError($this->firstValidationMessage($e));
        } catch (Throwable $e) {
            Log::error('PosIndex::updateCartItem failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'menu_item_id' => $menuItemId]);
            LivewireAlert::title('Unable to Update Quantity')
                ->text('Please refresh and try again.')
                ->error()
                ->show();
        }
    }

    public function removeCartItem($moduleId, $menuItemId)
    {
        $moduleId = (int) $moduleId;
        $this->cart = collect($this->cart)
            ->reject(fn ($line) => $line['menu_item_id'] === $menuItemId)
            ->values()
            ->toArray();

        $this->autofillPaymentAmount(null, 0);
    }

    public function addPaymentRow($moduleId = null): void
    {
        $this->splitPayments[] = ['method' => 'cash', 'amount' => 0, 'transaction_reference' => null];

        $this->autofillPaymentAmount(null, array_key_last($this->splitPayments));
    }

    public function removePaymentRow(int $index): void
    {
        unset($this->splitPayments[$index]);
        $this->splitPayments = array_values($this->splitPayments);

        if (empty($this->splitPayments)) {
            $this->addPaymentRow();
        }
    }

    public function updatedDiscount(): void
    {
        $this->discount = max(0, (float) $this->discount);
    }

    public function updatedDiscountId(): void
    {
        $this->discount_id = $this->discount_id ?: null;
    }

    public function updatedSaleType(): void
    {
        if ($this->sale_type !== 'dine_in') {
            $this->table_id = null;
        }
    }

    public function selectCustomer($customerId): void
    {
        $customer = Customer::query()
            ->where('branch_id', session('branch_id'))
            ->findOrFail($customerId);

        $this->customer_id = $customer->id;
        $this->customer_search = trim($customer->name.' '.($customer->phone ? '- '.$customer->phone : ''));
    }

    public function clearCustomer(): void
    {
        $this->customer_id = null;
        $this->customer_search = '';
    }

    public function autofillPaymentAmount($moduleId = null, int $index = 0): void
    {
        if (! isset($this->splitPayments[$index])) {
            return;
        }

        $method = $this->splitPayments[$index]['method'] ?? 'cash';

        if ($method === 'credit_sale') {
            $this->splitPayments[$index]['amount'] = 0;

            return;
        }

        $balance = $this->paymentBalance($index);
        $this->splitPayments[$index]['amount'] = max(0, round($balance, 2));
    }

    public function viewReceipt($saleId): void
    {
        $sale = Sale::accessible()
            ->with(['items', 'payments'])
            ->findOrFail($saleId);

        $this->receiptSaleId = $sale->id;
        $this->dispatch('open-modal', 'pos-receipt-modal');
    }

    public function loadSaleForEdit($saleId): void
    {
        $sale = Sale::accessible()
            ->with(['items.menuItem', 'items.module', 'customer'])
            ->findOrFail($saleId);

        abort_if($sale->status === 'refunded', 422, 'Cannot edit a refunded sale.');
        abort_if((float) $sale->paid_amount > 0, 422, 'Only unpaid sales can be edited in the POS. Please clear payments before editing.');

        $this->resetSaleForm();
        $this->editingSaleId = $sale->id;
        $this->editingSaleNumber = $sale->sale_number;
        $this->editingOriginalDiscount = (float) $sale->discount;
        $this->customer_id = $sale->customer_id;
        $this->customer_search = $sale->customer ? trim($sale->customer->name.' '.($sale->customer->phone ? '- '.$sale->customer->phone : '')) : '';
        $this->sale_type = $sale->type;
        $this->table_id = $sale->table_id;
        $this->notes = $sale->notes;
        $this->notifyKitchen = $sale->status === 'confirmed';
        $this->discount = (float) $sale->discount;
        $this->discount_id = null;
        $this->splitPayments = [
            ['method' => 'cash', 'amount' => 0, 'transaction_reference' => null],
        ];

        foreach ($sale->items as $item) {
            $this->cart[] = [
                'menu_item_id' => $item->menu_item_id,
                'module_id' => $item->module_id,
                'module_name' => $item->module?->name,
                'item_name' => $item->item_name,
                'qty' => (int) $item->qty,
                'unit_price' => (float) $item->unit_price,
                'tax' => (float) $item->tax,
                'discount' => (float) $item->discount,
                'subtotal' => (float) $item->subtotal,
            ];
        }
    }

    public function cancelEdit(): void
    {
        $this->resetSaleForm();
    }

    public function openRecentPayment($saleId): void
    {
        $branchId = session('branch_id');
        $sale = Sale::accessible()
            ->where('branch_id', $branchId)
            ->where('status', '!=', 'refunded')
            ->findOrFail($saleId);

        abort_if((float) $sale->remaining_balance <= 0, 422, 'This sale has no outstanding balance.');

        $this->recentPaymentSaleId = $sale->id;
        $this->recent_payment_method = 'cash';
        $this->recent_payment_amount = (float) $sale->remaining_balance;
        $this->recent_payment_reference = '';

        $this->dispatch('open-modal', 'recent-sale-payment-modal');
    }

    public function recordRecentPayment(): void
    {
        try {
            $this->validate([
                'recentPaymentSaleId' => 'required|exists:sales,id',
                'recent_payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank_transfer', 'wallet'])],
                'recent_payment_amount' => 'required|numeric|min:0.01',
                'recent_payment_reference' => 'nullable|string|max:255',
            ]);

            DB::transaction(function () {
                $sale = Sale::accessible()
                    ->with('items')
                    ->where('status', '!=', 'refunded')
                    ->lockForUpdate()
                    ->findOrFail($this->recentPaymentSaleId);

                $amount = round((float) $this->recent_payment_amount, 2);
                if ($amount > (float) $sale->remaining_balance) {
                    throw ValidationException::withMessages([
                        'recent_payment_amount' => 'Payment cannot exceed the outstanding balance.',
                    ]);
                }

                SalePaymentRecorder::recordCollection(
                    sale: $sale,
                    method: $this->recent_payment_method,
                    amount: $amount,
                    reference: $this->recent_payment_reference ?: null,
                    userId: (int) auth()->id(),
                    registerName: 'Auto-opened POS payment register',
                    notes: 'Outstanding payment for sale '.$sale->sale_number,
                );

                $paid = round((float) $sale->paid_amount + $amount, 2);
                $remaining = round((float) $sale->total - $paid - (float) $sale->refunded_amount, 2);

                $sale->update([
                    'paid_amount' => $paid,
                    'remaining_balance' => max(0, $remaining),
                    'is_debt' => $remaining > 0,
                    'status' => $remaining <= 0 ? 'completed' : $sale->status,
                    'completed_at' => $remaining <= 0 ? now() : $sale->completed_at,
                ]);

                if ($remaining <= 0) {
                    SaleTableRelease::releaseIfSettled($sale->fresh());
                }
            });

            $this->reset(['recentPaymentSaleId', 'recent_payment_reference']);
            $this->recent_payment_method = 'cash';
            $this->recent_payment_amount = 0;
            $this->dispatch('close-modal', 'recent-sale-payment-modal');

            LivewireAlert::title('Payment Recorded')
                ->text('The sale balance has been updated.')
                ->success()
                ->show();
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('recordRecentPayment failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            LivewireAlert::title('Error')
                ->text('Unable to record payment. Please try again or contact support.')
                ->error()
                ->show();
        }
    }

    public function releaseTable(int $saleId): void
    {
        try {
            DB::transaction(function () use ($saleId) {
                $sale = Sale::accessible()
                    ->with('table')
                    ->whereNotNull('table_id')
                    ->lockForUpdate()
                    ->findOrFail($saleId);

                abort_unless(SaleTableRelease::canRelease($sale), 422, 'Only completed or fully paid table orders can be released.');

                SaleTableRelease::release($sale);
            });

            LivewireAlert::title('Table Released')
                ->text('The dining table is now available.')
                ->success()
                ->show();
        } catch (Throwable $e) {
            Log::error('PosIndex::releaseTable failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'sale_id' => $saleId]);
            LivewireAlert::title('Unable to Release Table')
                ->text('Please refresh and try again.')
                ->error()
                ->show();
        }
    }

    public function createCustomer()
    {
        try {
            $this->validate([
                'new_customer_name' => 'required|string|max:255',
                'new_customer_email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('customers', 'email'),
                ],
                'new_customer_phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('customers', 'phone'),
                ],
                'new_customer_address' => 'nullable|string|max:500',
            ]);

            $branchId = session('branch_id');

            $customer = Customer::create([
                'branch_id' => $branchId,
                'name' => $this->new_customer_name,
                'email' => $this->new_customer_email,
                'phone' => $this->new_customer_phone,
                'address' => $this->new_customer_address,
                'status' => 'active',
            ]);

            $this->customer_id = $customer->id;
            $this->new_customer_name = null;
            $this->new_customer_email = null;
            $this->new_customer_phone = null;
            $this->new_customer_address = null;

            LivewireAlert::title('Customer Added')
                ->text('Customer registered successfully and selected for this sale.')
                ->success()
                ->show();
        } catch (Throwable $e) {
            Log::error('createCustomer failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            LivewireAlert::title('Error')
                ->text('Unable to add customer. Please try again.')
                ->error()
                ->show();
        }
    }

    public function saveSale()
    {
        $branchId = session('branch_id');
        $this->authorizeBranch($branchId);

        $cart = collect($this->cart);

        $this->withValidator(function ($validator) use ($cart) {
            $validator->after(function ($validator) use ($cart) {
                if ($cart->isEmpty()) {
                    $validator->errors()->add('cart', 'Add at least one menu item to the order.');
                }
            });
        })->validate([
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where(fn ($query) => $query->where('branch_id', $branchId)),
            ],
            'sale_type' => 'required|in:dine_in,takeaway,delivery,online',
            'table_id' => [
                'nullable',
                'required_if:sale_type,dine_in',
                Rule::exists('tables', 'id')->where(fn ($query) => $query->where('branch_id', $branchId)),
            ],
            'splitPayments' => 'array|min:1',
            'splitPayments.*.method' => 'required|in:cash,mobile_money,card,bank_transfer,wallet,credit_sale',
            'splitPayments.*.amount' => 'required|numeric|min:0',
            'splitPayments.*.transaction_reference' => 'nullable|string|max:255',
            'discount_id' => [
                'nullable',
                Rule::exists('discounts', 'id')->where(fn ($query) => $query->where('branch_id', $branchId)),
            ],
            'notes' => 'nullable|string|max:1000',
        ]);

        $cart->pluck('module_id')
            ->filter()
            ->unique()
            ->each(fn ($moduleId) => $this->authorizeModule($moduleId, $branchId));

        $menuItems = MenuItem::where('branch_id', $branchId)
            ->whereIn('id', $cart->pluck('menu_item_id')->unique())
            ->get()
            ->keyBy('id');

        if ($menuItems->count() !== $cart->pluck('menu_item_id')->unique()->count()) {
            throw ValidationException::withMessages([
                'cart' => 'One or more menu items are no longer available.',
            ]);
        }

        $cart = $cart->map(function ($line) use ($menuItems) {
            $menuItem = $menuItems->get($line['menu_item_id']);

            if (! $menuItem) {
                throw ValidationException::withMessages([
                    'cart' => 'One or more menu items are no longer available.',
                ]);
            }

            $qty = max(1, (int) $line['qty']);
            $unitPrice = round((float) $menuItem->price, 2);

            return [
                'menu_item_id' => $menuItem->id,
                'module_id' => $menuItem->module_id,
                'item_name' => $menuItem->name,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'tax' => 0,
                'discount' => 0,
                'subtotal' => round($qty * $unitPrice, 2),
            ];
        });

        $summary = $this->calculateOrderSummary(
            $cart,
            Module::whereIn('id', $cart->pluck('module_id')->unique())->get(),
            Tax::query()
                ->where('branch_id', $branchId)
                ->available()
                ->get(),
            Discount::query()
                ->where('branch_id', $branchId)
                ->available()
                ->get()
        );
        $subtotal = $summary['subtotal'];
        $serviceCharge = $summary['service_charge'];
        $tax = $summary['tax'];
        $discount = $summary['discount'];
        $total = $summary['total'];
        $payments = collect($this->splitPayments)
            ->map(fn ($payment) => [
                'method' => $payment['method'] ?? 'cash',
                'amount' => ($payment['method'] ?? 'cash') === 'credit_sale'
                    ? 0
                    : round((float) ($payment['amount'] ?? 0), 2),
                'transaction_reference' => $payment['transaction_reference'] ?? null,
            ])
            ->filter(fn ($payment) => $payment['method'] !== 'credit_sale' && $payment['amount'] > 0)
            ->values();

        $paidAmount = round($payments->sum('amount'), 2);

        if ($paidAmount > $total) {
            throw ValidationException::withMessages([
                'splitPayments' => 'Split payment amounts cannot be greater than the sale total.',
            ]);
        }

        $remaining = round($total - $paidAmount, 2);
        $isDebt = $remaining > 0;
        $saleStatus = $this->notifyKitchen ? 'confirmed' : ($isDebt ? 'served' : 'completed');
        $saleItemStatus = $this->notifyKitchen ? 'pending' : 'served';
        $editingSaleId = $this->editingSaleId;

        try {
            $sale = DB::transaction(function () use ($branchId, $cart, $subtotal, $tax, $discount, $serviceCharge, $total, $paidAmount, $remaining, $isDebt, $saleStatus, $saleItemStatus, $payments, $editingSaleId) {
                $existingSale = null;
                $originalCustomerId = null;
                $originalTotal = 0.0;
                if ($editingSaleId) {
                    $existingSale = Sale::accessible()
                        ->where('branch_id', $branchId)
                        ->where('status', '!=', 'refunded')
                        ->lockForUpdate()
                        ->with('items')
                        ->findOrFail($editingSaleId);

                    abort_if((float) $existingSale->paid_amount > 0, 422, 'Only unpaid sales can be edited in the POS.');
                    $originalCustomerId = $existingSale->customer_id;
                    $originalTotal = (float) $existingSale->total;
                }

                $existingItems = $existingSale
                    ? $existingSale->items->keyBy('menu_item_id')
                    : collect();
                $menuItemIdsToLock = $cart->pluck('menu_item_id')
                    ->merge($existingItems->pluck('menu_item_id'))
                    ->filter()
                    ->unique()
                    ->values();

                $lockedItems = MenuItem::where('branch_id', $branchId)
                    ->whereIn('id', $menuItemIdsToLock)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $table = null;
                if ($this->sale_type === 'dine_in' && $this->table_id) {
                    $tableQuery = DiningTable::where('branch_id', $branchId)
                        ->where('id', $this->table_id)
                        ->lockForUpdate();

                    if (! $existingSale || $existingSale->table_id !== $this->table_id) {
                        $tableQuery->where('status', 'available');
                    }

                    $table = $tableQuery->firstOrFail();
                }

                foreach ($cart as $line) {
                    $menuItem = $lockedItems->get($line['menu_item_id']);
                    $oldItem = $existingItems->get($line['menu_item_id']);
                    $qtyDifference = max(1, (int) $line['qty']) - (float) ($oldItem->qty ?? 0);

                    if (! $menuItem) {
                        throw ValidationException::withMessages([
                            'cart' => 'One or more menu items are no longer available.',
                        ]);
                    }

                    if (! $oldItem && $menuItem->status !== 'available') {
                        throw ValidationException::withMessages([
                            'cart' => "{$menuItem->name} is no longer available.",
                        ]);
                    }

                    if ($menuItem->is_trackable && $qtyDifference > (float) $menuItem->quantity) {
                        throw ValidationException::withMessages([
                            'cart' => "{$menuItem->name} does not have enough stock.",
                        ]);
                    }
                }

                $now = now();
                $sale = null;

                if ($existingSale) {
                    $sale = $existingSale;
                    $sale->update([
                        'customer_id' => $this->customer_id,
                        'type' => $this->sale_type,
                        'status' => $saleStatus,
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'discount' => $discount,
                        'service_charge' => $serviceCharge,
                        'total' => $total,
                        'paid_amount' => $paidAmount,
                        'remaining_balance' => $remaining,
                        'is_debt' => $isDebt,
                        'table_id' => $this->sale_type === 'dine_in' ? $this->table_id : null,
                        'notes' => $this->notes,
                        'served_at' => $saleStatus === 'served' ? ($sale->served_at ?: $now) : null,
                        'completed_at' => $saleStatus === 'completed' ? ($sale->completed_at ?: $now) : null,
                    ]);
                } else {
                    $sale = Sale::create([
                        'branch_id' => $branchId,
                        'customer_id' => $this->customer_id,
                        'created_by' => auth()->id(),
                        'sale_number' => strtoupper('S'.$now->format('YmdHis').Str::random(3)),
                        'type' => $this->sale_type,
                        'status' => $saleStatus,
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'discount' => $discount,
                        'service_charge' => $serviceCharge,
                        'total' => $total,
                        'paid_amount' => $paidAmount,
                        'remaining_balance' => $remaining,
                        'is_debt' => $isDebt,
                        'table_id' => $this->sale_type === 'dine_in' ? $this->table_id : null,
                        'notes' => $this->notes,
                        'sale_date' => $now,
                        'served_at' => $saleStatus === 'served' ? $now : null,
                        'completed_at' => $saleStatus === 'completed' ? $now : null,
                    ]);
                }

                $processedMenuItemIds = collect();

                foreach ($cart as $line) {
                    $menuItem = $lockedItems->get($line['menu_item_id']);
                    $oldItem = $existingItems->get($menuItem->id);
                    $newQty = max(1, (int) $line['qty']);
                    $qtyDifference = $newQty - ($oldItem->qty ?? 0);

                    if ($oldItem) {
                        $oldItem->update([
                            'item_name' => $line['item_name'],
                            'qty' => $newQty,
                            'unit_price' => $line['unit_price'],
                            'subtotal' => $line['subtotal'],
                            'total' => $line['subtotal'],
                            'status' => $saleItemStatus,
                            'is_kitchen_notified' => $this->notifyKitchen,
                            'kitchen_status' => $this->notifyKitchen ? 'queued' : 'completed',
                            'served_at' => $saleItemStatus === 'served' ? $now : null,
                        ]);
                    } else {
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'branch_id' => $branchId,
                            'module_id' => $menuItem->module_id,
                            'menu_item_id' => $menuItem->id,
                            'item_name' => $line['item_name'],
                            'sku' => null,
                            'qty' => $newQty,
                            'unit_price' => $line['unit_price'],
                            'unit_cost' => (float) ($menuItem->cost_price ?? 0),
                            'is_trackable' => (bool) $menuItem->is_trackable,
                            'tax' => 0,
                            'discount' => 0,
                            'subtotal' => $line['subtotal'],
                            'total' => $line['subtotal'],
                            'status' => $saleItemStatus,
                            'is_kitchen_notified' => $this->notifyKitchen,
                            'kitchen_status' => $this->notifyKitchen ? 'queued' : 'completed',
                            'notes' => null,
                            'served_at' => $saleItemStatus === 'served' ? $now : null,
                        ]);
                    }

                    if (abs((float) $qtyDifference) > 0.0001 && $menuItem->is_trackable) {
                        $quantityBefore = (int) ($menuItem->quantity ?? 0);
                        $quantityAfter = $quantityBefore - $qtyDifference;
                        $adjustmentType = $qtyDifference > 0 ? 'sale' : 'adjustment_increase';

                        abort_if($qtyDifference > 0 && $quantityAfter < 0, 422, "{$menuItem->name} does not have enough stock.");

                        MenuItemAdjustment::create([
                            'branch_id' => $branchId,
                            'module_id' => $menuItem->module_id,
                            'menu_item_id' => $menuItem->id,
                            'sale_id' => $sale->id,
                            'performed_by' => auth()->id(),
                            'type' => $adjustmentType,
                            'change_qty' => -1 * $qtyDifference,
                            'quantity_before' => $quantityBefore,
                            'quantity_after' => $quantityAfter,
                            'reference_no' => $sale->sale_number,
                            'notes' => ($editingSaleId ? 'Inventory adjustment for edited sale ' : 'Inventory reduction for sale ').$sale->sale_number,
                            'transaction_date' => $now,
                        ]);

                        $menuItem->update([
                            'quantity' => $quantityAfter,
                            'status' => $quantityAfter <= 0 ? 'out_of_stock' : $menuItem->status,
                        ]);
                    }

                    $processedMenuItemIds->push($menuItem->id);
                }

                $removedItems = $existingItems->reject(fn ($item) => $processedMenuItemIds->contains($item->menu_item_id));

                foreach ($removedItems as $removedItem) {
                    $menuItem = $lockedItems->get($removedItem->menu_item_id);

                    if ($menuItem && $menuItem->is_trackable) {
                        $quantityBefore = (int) ($menuItem->quantity ?? 0);
                        $quantityAfter = $quantityBefore + (int) $removedItem->qty;

                        $menuItem->update([
                            'quantity' => $quantityAfter,
                            'status' => $menuItem->status === 'out_of_stock' && $quantityAfter > 0 ? 'available' : $menuItem->status,
                        ]);

                        MenuItemAdjustment::create([
                            'branch_id' => $branchId,
                            'module_id' => $menuItem->module_id,
                            'menu_item_id' => $menuItem->id,
                            'sale_id' => $sale->id,
                            'performed_by' => auth()->id(),
                            'type' => 'adjustment_increase',
                            'quantity_before' => $quantityBefore,
                            'quantity_after' => $quantityAfter,
                            'change_qty' => (int) $removedItem->qty,
                            'reference_no' => $sale->sale_number,
                            'notes' => 'Inventory restoration for removed item on edited sale '.$sale->sale_number,
                            'transaction_date' => $now,
                        ]);
                    }

                    $removedItem->delete();
                }

                if ($existingSale && $existingSale->table_id && $existingSale->table_id !== ($this->sale_type === 'dine_in' ? $this->table_id : null)) {
                    DiningTable::whereKey($existingSale->table_id)->update(['status' => 'available']);
                }

                if ($table) {
                    $table->update(['status' => 'occupied']);
                }

                if ($paidAmount > 0) {
                    $sale->setRelation('items', $sale->items()->get(['module_id', 'subtotal']));

                    foreach ($payments as $payment) {
                        SalePaymentRecorder::recordCollection(
                            sale: $sale,
                            method: $payment['method'],
                            amount: (float) $payment['amount'],
                            reference: $payment['transaction_reference'],
                            userId: (int) auth()->id(),
                            registerName: 'Auto-opened POS register',
                            notes: 'Sale '.$sale->sale_number.' '.str_replace('_', ' ', $payment['method']).' payment',
                        );
                    }
                }

                if ($existingSale) {
                    if ((int) $originalCustomerId === (int) $this->customer_id) {
                        $delta = round($total - $originalTotal, 2);
                        if ($delta !== 0 && $this->customer_id) {
                            Customer::whereKey($this->customer_id)
                                ->update(['total_spent' => DB::raw('GREATEST(COALESCE(total_spent, 0) + '.$delta.', 0)')]);
                        }
                    } else {
                        if ($originalCustomerId) {
                            Customer::whereKey($originalCustomerId)
                                ->update([
                                    'total_orders' => DB::raw('GREATEST(COALESCE(total_orders, 0) - 1, 0)'),
                                    'total_spent' => DB::raw('GREATEST(COALESCE(total_spent, 0) - '.$originalTotal.', 0)'),
                                ]);
                        }
                        if ($this->customer_id) {
                            Customer::whereKey($this->customer_id)
                                ->update([
                                    'total_orders' => DB::raw('COALESCE(total_orders, 0) + 1'),
                                    'total_spent' => DB::raw('COALESCE(total_spent, 0) + '.$total),
                                    'last_order_at' => $now,
                                ]);
                        }
                    }
                } elseif ($this->customer_id) {
                    Customer::where('id', $this->customer_id)->update([
                        'total_orders' => DB::raw('COALESCE(total_orders, 0) + 1'),
                        'total_spent' => DB::raw('COALESCE(total_spent, 0) + '.$sale->total),
                        'last_order_at' => $now,
                    ]);
                }

                if ($saleStatus === 'completed') {
                    SaleTableRelease::releaseIfSettled($sale->fresh());
                }

                return $sale;
            });

            $this->resetSaleForm();

            if (! $isDebt) {
                $this->receiptSaleId = $sale->id;
                $this->dispatch('open-modal', 'pos-receipt-modal');
            }

            LivewireAlert::title($isDebt ? ($editingSaleId ? 'Sale Updated' : 'Sale Created') : ($editingSaleId ? 'Sale Updated' : 'Payment Complete'))
                ->text($isDebt ? 'Order recorded with an outstanding balance.' : ($editingSaleId ? 'Sale updated successfully.' : 'Order paid successfully. Receipt is ready.'))
                ->success()
                ->show();
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('saveSale failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            LivewireAlert::title('Error')
                ->text('Unable to save the sale. Please try again or contact support.')
                ->error()
                ->show();
        }
    }

    public function menuItemImageUrl(MenuItem $item): ?string
    {
        $path = trim((string) $item->image_url);

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            return asset(ltrim($path, '/'));
        }

        if (Str::startsWith($path, ['/'])) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    protected function receiptSettings(): array
    {
        return Cache::remember(PosCache::receiptSettingsKey(), now()->addMinutes(5), function () {
            $settings = WebsiteContent::settings();

            return [
                'business_name' => $settings?->business_name ?: config('app.name', 'Cazera'),
                'logo' => $settings?->logo ? WebsiteContent::assetPath($settings->logo) : null,
                'tagline' => $settings?->tagline,
                'address' => $settings?->address,
                'phone' => $settings?->phone,
                'email' => $settings?->email,
                'whatsapp' => $settings?->whatsapp,
            ];
        });
    }

    protected function calculateOrderSummary($cart = null, $modules = null, $taxes = null, $discounts = null): array
    {
        $branchId = session('branch_id');
        $cart = collect($cart ?? $this->cart);
        $moduleIds = $cart->pluck('module_id')->filter()->unique()->values();
        $modules = collect($modules ?? Module::whereIn('id', $moduleIds)->get())->keyBy('id');
        $taxes = collect($taxes ?? Tax::query()
            ->where('branch_id', $branchId)
            ->available()
            ->get());
        $discounts = collect($discounts ?? Discount::query()
            ->where('branch_id', $branchId)
            ->available()
            ->get());
        $taxRate = (float) $taxes->sum('rate_percent') / 100;

        $moduleSummaries = $cart
            ->groupBy('module_id')
            ->map(function ($lines, $moduleId) use ($modules, $taxRate) {
                $subtotal = round((float) $lines->sum('subtotal'), 2);
                $module = $modules->get((int) $moduleId);
                $serviceChargeRate = data_get($module?->pos_settings, 'service_charge', 0) / 100;
                $serviceCharge = round($subtotal * $serviceChargeRate, 2);
                $billBeforeDiscount = round($subtotal + $serviceCharge, 2);
                $tax = round($billBeforeDiscount * $taxRate, 2);

                return [
                    'module_id' => (int) $moduleId,
                    'module_name' => $module?->name ?? $lines->first()['module_name'] ?? 'Module',
                    'subtotal' => $subtotal,
                    'service_charge' => $serviceCharge,
                    'tax_rate' => $taxRate,
                    'tax' => $tax,
                    'discount' => 0.0,
                    'total' => round(max(0, $billBeforeDiscount + $tax), 2),
                    'items' => $lines->sum('qty'),
                ];
            })
            ->values();

        $subtotal = round((float) $moduleSummaries->sum('subtotal'), 2);
        $serviceCharge = round((float) $moduleSummaries->sum('service_charge'), 2);
        $tax = round((float) $moduleSummaries->sum('tax'), 2);
        $billBeforeDiscount = round($subtotal + $serviceCharge, 2);
        $selectedDiscount = $this->discount_id
            ? $discounts->firstWhere('id', (int) $this->discount_id)
            : null;
        $discount = $selectedDiscount
            ? $selectedDiscount->calculateFor($billBeforeDiscount)
            : 0.0;

        if ($this->editingSaleId && ! $this->discount_id) {
            $discount = (float) $this->editingOriginalDiscount;
        }

        if ($discount > 0 && $moduleSummaries->isNotEmpty()) {
            $basis = max((float) $moduleSummaries->sum('subtotal'), 0.01);
            $allocated = 0.0;
            $lastKey = $moduleSummaries->keys()->last();

            $moduleSummaries = $moduleSummaries->map(function (array $summary, int $key) use ($discount, $basis, &$allocated, $lastKey) {
                $moduleDiscount = $key === $lastKey
                    ? round($discount - $allocated, 2)
                    : round($discount * ((float) $summary['subtotal'] / $basis), 2);
                $allocated += $moduleDiscount;
                $summary['discount'] = $moduleDiscount;
                $summary['total'] = round(max(0, (float) $summary['total'] - $moduleDiscount), 2);

                return $summary;
            });
        }

        $total = round(max(0, $subtotal + $serviceCharge + $tax - $discount), 2);
        $paid = round((float) collect($this->splitPayments)
            ->filter(fn ($payment) => ($payment['method'] ?? 'cash') !== 'credit_sale')
            ->sum(fn ($payment) => (float) ($payment['amount'] ?? 0)), 2);

        return [
            'subtotal' => $subtotal,
            'service_charge' => $serviceCharge,
            'tax' => $tax,
            'discount' => round((float) $discount, 2),
            'total' => $total,
            'paid' => $paid,
            'remaining' => round(max($total - $paid, 0), 2),
            'module_summaries' => $moduleSummaries,
        ];
    }

    protected function paymentBalance(?int $ignoreIndex = null): float
    {
        $summary = $this->calculateOrderSummary();
        $total = $summary['total'];

        $paidByOtherRows = collect($this->splitPayments)
            ->reject(fn ($payment, $index) => $ignoreIndex !== null && $index === $ignoreIndex)
            ->filter(fn ($payment) => in_array($payment['method'] ?? 'cash', $this->cashPaymentMethods, true))
            ->sum(fn ($payment) => (float) ($payment['amount'] ?? 0));

        return round($total - $paidByOtherRows, 2);
    }

    protected function firstValidationMessage(ValidationException $e): string
    {
        return collect($e->errors())->flatten()->first() ?: 'Please check the cart and try again.';
    }

    protected function showCartError(string $message): void
    {
        $this->resetErrorBag('cart');
        $this->addError('cart', $message);

        LivewireAlert::title('Stock Limit')
            ->text($message)
            ->warning()
            ->show();
    }

    public function displayTaxAmount(int $branchId, int $moduleId, float $billAmount): float
    {
        $rate = Tax::query()
            ->where('branch_id', $branchId)
            ->available()
            ->sum('rate_percent');

        return round($billAmount * ((float) $rate / 100), 2);
    }

    protected function receiptTaxBreakdown(?Sale $sale)
    {
        if (! $sale) {
            return collect();
        }

        $taxes = Tax::query()
            ->where('branch_id', $sale->branch_id)
            ->available()
            ->orderBy('name')
            ->get();

        if ($taxes->isEmpty()) {
            return (float) $sale->tax > 0
                ? collect([[
                    'name' => 'Tax',
                    'rate' => null,
                    'amount' => (float) $sale->tax,
                ]])
                : collect();
        }

        $basis = round((float) $sale->subtotal + (float) $sale->service_charge, 2);
        $lines = $taxes->map(fn (Tax $tax) => [
            'name' => $tax->name,
            'rate' => (float) $tax->rate_percent,
            'amount' => round($basis * ((float) $tax->rate_percent / 100), 2),
        ]);

        $difference = round((float) $sale->tax - $lines->sum('amount'), 2);

        if ($lines->isNotEmpty() && abs($difference) >= 0.01) {
            $lastKey = $lines->keys()->last();
            $lastLine = $lines->get($lastKey);
            $lastLine['amount'] = round($lastLine['amount'] + $difference, 2);
            $lines->put($lastKey, $lastLine);
        }

        return $lines;
    }

    public function selectedDiscountAmount(int $branchId, int $moduleId, float $billAmount): float
    {
        if (! $this->discount_id) {
            return $this->editingSaleId ? $this->editingOriginalDiscount : 0.0;
        }

        $discount = Discount::query()
            ->where('branch_id', $branchId)
            ->available()
            ->find($this->discount_id);

        return $discount ? $discount->calculateFor($billAmount) : 0.0;
    }
}
