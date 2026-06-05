<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\MenuItem;
use App\Models\MenuItemAdjustment;
use App\Models\Module;
use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Tax;
use App\Support\WebsiteContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

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
    public $cart = [];
    public $receiptSaleId;
    public $recentPaymentSaleId;
    public $recent_payment_method = 'cash';
    public $recent_payment_amount = 0;
    public $recent_payment_reference = '';

    protected array $cashPaymentMethods = ['cash', 'mobile_money', 'card', 'bank_transfer', 'wallet'];

    public function mount()
    {
        $this->payment_method = 'cash';
        $this->sale_type = 'dine_in';
        $this->payment_amount = 0;
        $this->splitPayments = [
            ['method' => 'cash', 'amount' => 0, 'transaction_reference' => null],
        ];
        $this->discount = 0;
        $this->discount_id = null;
        $this->notes = '';
        $this->notifyKitchen = false;
    }

    public function render()
    {
        $branchId = session('branch_id');
        if ($branchId) {
            $this->authorizeBranch($branchId);
        }

        $modules = $this->getAccessibleModules($branchId);

        $menuItems = collect();
        $taxesByModule = collect();
        $discountsByModule = collect();

        foreach ($modules as $module) {
            $search = trim((string) data_get($this->menuSearch, $module->id, ''));

            $menuItems[$module->id] = MenuItem::where('branch_id', $branchId)
                ->where('module_id', $module->id)
                ->where('status', 'available')
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($subQuery) => $subQuery->where('name', 'like', "%{$search}%"));
                }))
                ->orderBy('name')
                ->get();

            $taxesByModule[$module->id] = Tax::query()
                ->where('branch_id', $branchId)
                ->where('module_id', $module->id)
                ->available()
                ->orderBy('name')
                ->get();

            $discountsByModule[$module->id] = Discount::query()
                ->where('branch_id', $branchId)
                ->where('module_id', $module->id)
                ->available()
                ->orderBy('name')
                ->get();
        }

        $receiptSale = $this->receiptSaleId
            ? Sale::with(['branch', 'module', 'customer', 'creator', 'items', 'payments.receiver'])->find($this->receiptSaleId)
            : null;

        return view('livewire.backoffice.pos.index', [
            'modules' => $modules,
            'menuItemsByModule' => $menuItems,
            'taxesByModule' => $taxesByModule,
            'discountsByModule' => $discountsByModule,
            'customers' => Customer::query()
                ->when(trim($this->customer_search), function ($query) {
                    $search = trim($this->customer_search);

                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->limit(25)
                ->get(),
            'lastSales' => Sale::with(['customer', 'module', 'creator', 'payments', 'latestPayment.receiver'])
                ->where('branch_id', $branchId)
                ->where('status', '!=', 'refunded')
                ->when($modules->pluck('id')->isNotEmpty(), fn($query) => $query->whereIn('module_id', $modules->pluck('id')))
                ->latest('sale_date')
                ->take(20)
                ->get(),
            'receiptSale' => $receiptSale,
            'receiptTaxes' => $this->receiptTaxBreakdown($receiptSale),
            'recentPaymentSale' => $this->recentPaymentSaleId
                ? Sale::with(['customer', 'branch', 'module'])->accessible()->find($this->recentPaymentSaleId)
                : null,
            'receiptSettings' => $this->receiptSettings(),
            'branchId' => $branchId,
        ]);
    }

    public function getAccessibleModules($branchId)
    {
        if (! $branchId) {
            return collect();
        }

        return $this->accessibleModules($branchId, 'pos');
    }

    public function addToCart($menuItemId, $moduleId)
    {
        $moduleId = (int) $moduleId;
        $branchId = session('branch_id');
        $this->authorizeModule($moduleId, $branchId);

        $item = MenuItem::where('branch_id', $branchId)
            ->where('module_id', $moduleId)
            ->where('status', 'available')
            ->findOrFail($menuItemId);

        abort_if($item->is_trackable && (float) $item->quantity <= 0, 422, 'This item is out of stock.');

        $cart = collect($this->cart[$moduleId] ?? []);

        $existing = $cart->firstWhere('menu_item_id', $item->id);

        if ($existing) {
            $cart = $cart->map(function ($line) use ($item) {
                if ($line['menu_item_id'] === $item->id) {
                    abort_if($item->is_trackable && ($line['qty'] + 1) > (float) $item->quantity, 422, 'Requested quantity is above available stock.');
                    $line['qty'] += 1;
                    $line['subtotal'] = $line['qty'] * $line['unit_price'];
                }

                return $line;
            });
        } else {
            $cart->push([
                'menu_item_id' => $item->id,
                'item_name' => $item->name,
                'qty' => 1,
                'unit_price' => (float) $item->price,
                'tax' => 0,
                'discount' => 0,
                'subtotal' => (float) $item->price,
            ]);
        }

        $this->cart = array_replace($this->cart, [
            $moduleId => $cart->values()->toArray(),
        ]);

        $this->autofillPaymentAmount($moduleId, 0);
    }

    public function updateCartItem($moduleId, $menuItemId, $qty)
    {
        $moduleId = (int) $moduleId;
        $branchId = session('branch_id');
        $this->authorizeModule($moduleId, $branchId);
        $qty = max(1, intval($qty));
        $item = MenuItem::where('branch_id', $branchId)
            ->where('module_id', $moduleId)
            ->where('status', 'available')
            ->findOrFail($menuItemId);
        abort_if($item->is_trackable && $qty > (float) $item->quantity, 422, 'Requested quantity is above available stock.');

        $cart = collect($this->cart[$moduleId] ?? [])->map(function ($line) use ($menuItemId, $qty) {
            if ($line['menu_item_id'] === $menuItemId) {
                $line['qty'] = $qty;
                $line['subtotal'] = $qty * $line['unit_price'];
            }

            return $line;
        });

        $this->cart = array_replace($this->cart, [
            $moduleId => $cart->values()->toArray(),
        ]);

        $this->autofillPaymentAmount($moduleId, 0);
    }

    public function removeCartItem($moduleId, $menuItemId)
    {
        $moduleId = (int) $moduleId;
        $this->cart = array_replace($this->cart, [
            $moduleId => collect($this->cart[$moduleId] ?? [])
            ->reject(fn($line) => $line['menu_item_id'] === $menuItemId)
            ->values()
            ->toArray(),
        ]);

        $this->autofillPaymentAmount($moduleId, 0);
    }

    public function addPaymentRow($moduleId = null): void
    {
        $this->splitPayments[] = ['method' => 'cash', 'amount' => 0, 'transaction_reference' => null];

        if ($moduleId) {
            $this->autofillPaymentAmount((int) $moduleId, array_key_last($this->splitPayments));
        }
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

    public function selectCustomer($customerId): void
    {
        $customer = Customer::findOrFail($customerId);

        $this->customer_id = $customer->id;
        $this->customer_search = trim($customer->name . ' ' . ($customer->phone ? '- ' . $customer->phone : ''));
    }

    public function clearCustomer(): void
    {
        $this->customer_id = null;
        $this->customer_search = '';
    }

    public function autofillPaymentAmount($moduleId, int $index): void
    {
        if (! isset($this->splitPayments[$index])) {
            return;
        }

        $method = $this->splitPayments[$index]['method'] ?? 'cash';

        if ($method === 'credit_sale') {
            $this->splitPayments[$index]['amount'] = 0;
            return;
        }

        $balance = $this->paymentBalanceForModule((int) $moduleId, $index);
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
        $this->validate([
            'recentPaymentSaleId' => 'required|exists:sales,id',
            'recent_payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank_transfer', 'wallet'])],
            'recent_payment_amount' => 'required|numeric|min:0.01',
            'recent_payment_reference' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () {
            $sale = Sale::accessible()
                ->where('status', '!=', 'refunded')
                ->lockForUpdate()
                ->findOrFail($this->recentPaymentSaleId);

            $amount = round((float) $this->recent_payment_amount, 2);
            abort_if($amount > (float) $sale->remaining_balance, 422, 'Payment cannot exceed outstanding balance.');

            $cashRegister = $this->openRegisterFor($sale->branch_id, $sale->module_id, 'Auto-opened POS payment register');

            CashRegisterTransaction::create([
                'cash_register_id' => $cashRegister->id,
                'branch_id' => $sale->branch_id,
                'module_id' => $sale->module_id,
                'sale_id' => $sale->id,
                'performed_by' => auth()->id(),
                'type' => 'sale',
                'amount' => $amount,
                'notes' => 'Outstanding payment for sale ' . $sale->sale_number,
                'transaction_date' => now(),
            ]);

            $cashRegister->addExpectedBalanceForTransaction('sale', $amount);

            Payment::create([
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'module_id' => $sale->module_id,
                'cash_register_id' => $cashRegister->id,
                'received_by' => auth()->id(),
                'method' => $this->recent_payment_method,
                'amount' => $amount,
                'transaction_reference' => $this->recent_payment_reference ?: null,
                'status' => 'completed',
                'notes' => 'Outstanding sale payment recorded from POS recent sales.',
                'paid_at' => now(),
            ]);

            $paid = round((float) $sale->paid_amount + $amount, 2);
            $remaining = round((float) $sale->total - $paid, 2);

            $sale->update([
                'paid_amount' => $paid,
                'remaining_balance' => max(0, $remaining),
                'is_debt' => $remaining > 0,
                'status' => $remaining <= 0 ? 'completed' : $sale->status,
                'completed_at' => $remaining <= 0 ? now() : $sale->completed_at,
            ]);
        });

        $this->reset(['recentPaymentSaleId', 'recent_payment_reference']);
        $this->recent_payment_method = 'cash';
        $this->recent_payment_amount = 0;
        $this->dispatch('close-modal', 'recent-sale-payment-modal');

        LivewireAlert::title('Payment Recorded')
            ->text('The sale balance has been updated.')
            ->success()
            ->show();
    }

    public function createCustomer()
    {
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
    }

    public function saveSale($moduleId)
    {
        $branchId = session('branch_id');
        $this->authorizeBranch($branchId);
        $this->authorizeModule($moduleId, $branchId);

        $cart = collect($this->cart[$moduleId] ?? []);

        if ($cart->isEmpty()) {
            LivewireAlert::title('No Items')
                ->text('Add at least one menu item to the order.')
                ->warning()
                ->show();

            return;
        }

        $this->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'sale_type' => 'required|in:dine_in,takeaway,delivery,online',
            'splitPayments' => 'array|min:1',
            'splitPayments.*.method' => 'required|in:cash,mobile_money,card,bank_transfer,wallet,credit_sale',
            'splitPayments.*.amount' => 'required|numeric|min:0',
            'splitPayments.*.transaction_reference' => 'nullable|string|max:255',
            'discount_id' => 'nullable|exists:discounts,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $module = Module::findOrFail($moduleId);
        $menuItems = MenuItem::where('branch_id', $branchId)
            ->where('module_id', $module->id)
            ->whereIn('id', $cart->pluck('menu_item_id')->unique())
            ->get()
            ->keyBy('id');

        abort_if($menuItems->count() !== $cart->pluck('menu_item_id')->unique()->count(), 422, 'One or more menu items are no longer available.');

        $cart = $cart->map(function ($line) use ($menuItems) {
            $menuItem = $menuItems->get($line['menu_item_id']);

            abort_if(! $menuItem || $menuItem->status !== 'available', 422, 'One or more menu items are no longer available.');
            abort_if($menuItem->is_trackable && (float) $line['qty'] > (float) $menuItem->quantity, 422, "{$menuItem->name} does not have enough stock.");

            $qty = max(1, (int) $line['qty']);
            $unitPrice = round((float) $menuItem->price, 2);

            return [
                'menu_item_id' => $menuItem->id,
                'item_name' => $menuItem->name,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'tax' => 0,
                'discount' => 0,
                'subtotal' => round($qty * $unitPrice, 2),
            ];
        });

        $subtotal = round((float) $cart->sum('subtotal'), 2);
        $serviceChargeRate = data_get($module->pos_settings, 'service_charge', 0) / 100;
        $serviceCharge = round($subtotal * $serviceChargeRate, 2);
        $billBeforeDiscount = round($subtotal + $serviceCharge, 2);
        $tax = $this->displayTaxAmount($branchId, $module->id, $billBeforeDiscount);
        $discount = $this->selectedDiscountAmount($branchId, $module->id, $billBeforeDiscount);
        $total = round(max(0, $billBeforeDiscount - $discount), 2);
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
            LivewireAlert::title('Payment Exceeds Total')
                ->text('Split payment amounts cannot be greater than the sale total.')
                ->warning()
                ->show();

            return;
        }

        $remaining = round($total - $paidAmount, 2);
        $isDebt = $remaining > 0;
        $saleStatus = $this->notifyKitchen ? 'confirmed' : ($isDebt ? 'served' : 'completed');
        $saleItemStatus = $this->notifyKitchen ? 'pending' : 'served';

        $sale = DB::transaction(function () use ($branchId, $module, $cart, $subtotal, $tax, $discount, $serviceCharge, $total, $paidAmount, $remaining, $isDebt, $saleStatus, $saleItemStatus, $payments) {
            $lockedItems = MenuItem::where('branch_id', $branchId)
                ->where('module_id', $module->id)
                ->whereIn('id', $cart->pluck('menu_item_id')->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart as $line) {
                $menuItem = $lockedItems->get($line['menu_item_id']);
                abort_if(! $menuItem || $menuItem->status !== 'available', 422, 'One or more menu items are no longer available.');
                abort_if($menuItem->is_trackable && (float) $line['qty'] > (float) $menuItem->quantity, 422, "{$menuItem->name} does not have enough stock.");
            }

            $now = now();
            $sale = Sale::create([
                'branch_id' => $branchId,
                'module_id' => $module->id,
                'customer_id' => $this->customer_id,
                'created_by' => auth()->id(),
                'sale_number' => strtoupper('S' . $now->format('YmdHis') . Str::random(3)),
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
                'notes' => $this->notes,
                'sale_date' => $now,
                'served_at' => $saleStatus === 'served' ? $now : null,
                'completed_at' => $saleStatus === 'completed' ? $now : null,
            ]);

            foreach ($cart as $line) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'branch_id' => $branchId,
                    'module_id' => $module->id,
                    'menu_item_id' => $line['menu_item_id'],
                    'item_name' => $line['item_name'],
                    'sku' => null,
                    'qty' => $line['qty'],
                    'unit_price' => $line['unit_price'],
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

                $menuItem = $lockedItems->get($line['menu_item_id']);

                if ($menuItem->is_trackable) {
                    $quantityBefore = (int) ($menuItem->quantity ?? 0);
                    $quantityAfter = $quantityBefore - (int) $line['qty'];

                    MenuItemAdjustment::create([
                        'branch_id' => $branchId,
                        'module_id' => $module->id,
                        'menu_item_id' => $menuItem->id,
                        'sale_id' => $sale->id,
                        'performed_by' => auth()->id(),
                        'type' => 'sale',
                        'change_qty' => -1 * abs((int) $line['qty']),
                        'quantity_before' => $quantityBefore,
                        'quantity_after' => $quantityAfter,
                        'reference_no' => $sale->sale_number,
                        'notes' => 'Inventory reduction for sale ' . $sale->sale_number,
                        'transaction_date' => $now,
                    ]);

                    $menuItem->update([
                        'quantity' => $quantityAfter,
                        'status' => $quantityAfter <= 0 ? 'out_of_stock' : $menuItem->status,
                    ]);
                }
            }

            if ($paidAmount > 0) {
                $cashRegister = $this->openRegisterFor($branchId, $module->id, 'Auto-opened POS register');

                foreach ($payments as $payment) {
                    CashRegisterTransaction::create([
                        'cash_register_id' => $cashRegister->id,
                        'branch_id' => $branchId,
                        'module_id' => $module->id,
                        'sale_id' => $sale->id,
                        'performed_by' => auth()->id(),
                        'type' => 'sale',
                        'amount' => $payment['amount'],
                        'notes' => 'Sale ' . $sale->sale_number . ' ' . str_replace('_', ' ', $payment['method']) . ' payment',
                        'transaction_date' => $now,
                    ]);

                    $cashRegister->addExpectedBalanceForTransaction('sale', $payment['amount']);

                    Payment::create([
                        'sale_id' => $sale->id,
                        'branch_id' => $branchId,
                        'module_id' => $module->id,
                        'cash_register_id' => $cashRegister->id,
                        'received_by' => auth()->id(),
                        'method' => $payment['method'],
                        'amount' => $payment['amount'],
                        'transaction_reference' => $payment['transaction_reference'],
                        'status' => 'completed',
                        'notes' => null,
                        'paid_at' => $now,
                    ]);
                }
            }

            if ($this->customer_id) {
                Customer::where('id', $this->customer_id)->update([
                    'total_orders' => DB::raw('COALESCE(total_orders, 0) + 1'),
                    'total_spent' => DB::raw('COALESCE(total_spent, 0) + ' . $sale->total),
                    'last_order_at' => $now,
                ]);
            }

            return $sale;
        });

        $this->cart[$moduleId] = [];
        $this->payment_amount = 0;
        $this->splitPayments = [
            ['method' => 'cash', 'amount' => 0, 'transaction_reference' => null],
        ];
        $this->discount = 0;
        $this->discount_id = null;
        $this->notes = null;
        $this->notifyKitchen = false;
        $this->status = 'pending';

        if (! $isDebt) {
            $this->receiptSaleId = $sale->id;
            $this->dispatch('open-modal', 'pos-receipt-modal');
        }

        LivewireAlert::title($isDebt ? 'Sale Created' : 'Payment Complete')
            ->text($isDebt ? 'Order recorded with an outstanding balance.' : 'Order paid successfully. Receipt is ready.')
            ->success()
            ->show();
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

        return asset('storage/' . ltrim($path, '/'));
    }

    protected function receiptSettings(): array
    {
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
    }

    protected function paymentBalanceForModule(int $moduleId, ?int $ignoreIndex = null): float
    {
        $module = Module::find($moduleId);
        $branchId = session('branch_id');
        $cart = collect($this->cart[$moduleId] ?? []);

        if (! $module || $cart->isEmpty()) {
            return 0.0;
        }

        $subtotal = $cart->sum('subtotal');
        $serviceChargeRate = data_get($module->pos_settings, 'service_charge', 0) / 100;
        $serviceCharge = round($subtotal * $serviceChargeRate, 2);
        $billBeforeDiscount = round($subtotal + $serviceCharge, 2);
        $discount = $this->selectedDiscountAmount((int) $branchId, $moduleId, $billBeforeDiscount);
        $total = round(max(0, $billBeforeDiscount - $discount), 2);

        $paidByOtherRows = collect($this->splitPayments)
            ->reject(fn ($payment, $index) => $ignoreIndex !== null && $index === $ignoreIndex)
            ->filter(fn ($payment) => in_array($payment['method'] ?? 'cash', $this->cashPaymentMethods, true))
            ->sum(fn ($payment) => (float) ($payment['amount'] ?? 0));

        return round($total - $paidByOtherRows, 2);
    }

    protected function openRegisterFor(int $branchId, int $moduleId, string $name): CashRegister
    {
        $register = CashRegister::where('branch_id', $branchId)
            ->where('module_id', $moduleId)
            ->where('is_open', true)
            ->latest('opened_at')
            ->first();

        return $register ?: CashRegister::create([
            'branch_id' => $branchId,
            'module_id' => $moduleId,
            'opened_by' => auth()->id(),
            'name' => $name,
        ]);
    }

    public function displayTaxAmount(int $branchId, int $moduleId, float $billAmount): float
    {
        $rate = Tax::query()
            ->where('branch_id', $branchId)
            ->where('module_id', $moduleId)
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
            ->where('module_id', $sale->module_id)
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
            return 0.0;
        }

        $discount = Discount::query()
            ->where('branch_id', $branchId)
            ->where('module_id', $moduleId)
            ->available()
            ->find($this->discount_id);

        return $discount ? $discount->calculateFor($billAmount) : 0.0;
    }
}
