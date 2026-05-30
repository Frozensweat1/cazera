<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Point of Sale</h1>
                <p class="text-gray-500">Create orders by module, register customers quickly, and monitor the latest
                    sales.</p>
            </div>
        </div>

        @if (empty($branchId))
            <div class="panel">
                <p class="text-yellow-600">Please select a branch to use the POS system.</p>
            </div>
        @elseif ($modules->isEmpty())
            <div class="panel">
                <p class="text-gray-600">No POS modules are assigned to your account for this branch.</p>
            </div>
        @else
            <div class="panel">
                <x-ui.tabs name="pos-modules" default="module-{{ $modules->first()->id }}">
                    <x-slot name="headers">
                        @foreach ($modules as $module)
                            <li data-tab="module-{{ $module->id }}" class="relative" x-init="$nextTick(() => $root.__tabs?.updateIndicator?.())">
                                <a href="#"
                                    class="p-5 py-3 flex items-center relative transition-colors duration-200"
                                    :class="activeTab === 'module-{{ $module->id }}' ? 'text-secondary' : ''"
                                    @click.prevent="setTab('module-{{ $module->id }}')">
                                    {{ $module->name }}
                                </a>
                            </li>
                        @endforeach
                    </x-slot>

                    @foreach ($modules as $module)
                        <div x-show="activeTab === 'module-{{ $module->id }}'" x-transition.opacity.duration.200ms
                            class="mt-4">
                            <div class="grid grid-cols-1 xl:grid-cols-[1.7fr_1.3fr] gap-6">
                                <div class="space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div>
                                            <h2 class="text-lg font-semibold">{{ $module->name }} menu</h2>
                                            <p class="text-sm text-gray-500">Browse available menu items for this POS
                                                module.</p>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <x-ui.input name="menuSearch{{ $module->id }}"
                                                wire:model.live.debounce.300ms="menuSearch.{{ $module->id }}"
                                                placeholder="Search menu items..." />
                                            <div class="relative z-50">
                                                <x-ui.input name="customer_search"
                                                    wire:model.live.debounce.300ms="customer_search"
                                                    placeholder="Search customer or leave walk-in..." autocomplete="off" />
                                                @if ($customer_id)
                                                    <button type="button"
                                                        class="absolute inset-y-0 right-2 my-auto h-7 rounded-md px-2 text-xs font-semibold text-danger hover:bg-danger-light"
                                                        wire:click="clearCustomer">Clear</button>
                                                @elseif (trim($customer_search) !== '')
                                                    <div
                                                        class="absolute z-[9999] mt-2 max-h-64 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-[#0e1726]">
                                                        @forelse ($customers as $customer)
                                                            <button type="button"
                                                                class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-50 dark:hover:bg-white/5"
                                                                wire:click="selectCustomer({{ $customer->id }})">
                                                                <span class="block font-semibold text-slate-900 dark:text-white-light">{{ $customer->name }}</span>
                                                                <span class="block text-xs text-slate-500">
                                                                    {{ collect([$customer->phone, $customer->email])->filter()->implode(' - ') ?: 'No contact saved' }}
                                                                </span>
                                                            </button>
                                                        @empty
                                                            <div class="px-4 py-3 text-sm text-slate-500">No customer found.</div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @forelse ($menuItemsByModule[$module->id] ?? collect() as $item)
                                            @php
                                                $imageUrl = $this->menuItemImageUrl($item);
                                            @endphp
                                            <div class="panel flex gap-4 p-4">
                                                <div
                                                    class="h-24 w-24 flex-none overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $item->name }}"
                                                            class="h-full w-full object-cover" loading="lazy">
                                                    @else
                                                        <div
                                                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-50 to-slate-100 text-slate-500">
                                                            <svg viewBox="0 0 96 96" class="h-16 w-16"
                                                                fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <circle cx="35" cy="48" r="22"
                                                                    fill="#f8fafc" stroke="currentColor"
                                                                    stroke-width="4" />
                                                                <circle cx="35" cy="48" r="10"
                                                                    fill="#f59e0b" opacity=".35" />
                                                                <path d="M67 18h10l4 48a9 9 0 0 1-18 0l4-48Z"
                                                                    fill="#e0f2fe" stroke="currentColor"
                                                                    stroke-width="4" />
                                                                <path d="M66 34h13" stroke="currentColor"
                                                                    stroke-width="4" stroke-linecap="round" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex min-w-0 flex-1 flex-col justify-between gap-3">
                                                    <div>
                                                        <h3 class="font-semibold">{{ $item->name }}</h3>
                                                        <p class="line-clamp-2 text-sm text-gray-500">
                                                            {{ $item->description }}</p>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-4">
                                                        <div>
                                                            <p class="text-sm font-semibold text-gray-800">
                                                                {{ number_format($item->price, 2) }}</p>
                                                            <p class="text-xs text-gray-400">
                                                                {{ $item->is_trackable ? number_format($item->quantity, 0) . ' available' : 'Always available' }}
                                                            </p>
                                                        </div>
                                                        <x-ui.button
                                                            wire:click="addToCart({{ $item->id }},{{ $module->id }})"
                                                            target="addToCart({{ $item->id }},{{ $module->id }})"
                                                            type="button" icon="plus">Add</x-ui.button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="panel md:col-span-2 py-10 text-center text-gray-500">
                                                No menu items match your search.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="panel">
                                        <h3 class="text-lg font-semibold">Order summary</h3>

                                        <div class="space-y-3 mt-4">
                                            @php
                                                $moduleCart = collect($this->cart[$module->id] ?? []);
                                                $subtotal = $moduleCart->sum('subtotal');
                                                $taxRate = data_get($module->pos_settings, 'tax_rate', 0) / 100;
                                                $serviceChargeRate =
                                                    data_get($module->pos_settings, 'service_charge', 0) / 100;
                                                $tax = round($subtotal * $taxRate, 2);
                                                $serviceCharge = round($subtotal * $serviceChargeRate, 2);
                                                $total = round($subtotal + $tax + $serviceCharge - $discount, 2);
                                                    $paidPreview = collect($splitPayments)
                                                        ->filter(fn($payment) => ($payment['method'] ?? 'cash') !== 'credit_sale')
                                                        ->sum(fn($payment) => (float) ($payment['amount'] ?? 0));
                                                $remaining = round(max($total - $paidPreview, 0), 2);
                                            @endphp

                                            <div class="overflow-x-auto">
                                                <table class="w-full text-left text-sm">
                                                    <thead>
                                                        <tr class="text-slate-600 border-b border-slate-200">
                                                            <th>Item</th>
                                                            <th class="text-center">Qty</th>
                                                            <th class="text-right">Price</th>
                                                            <th class="text-right">Subtotal</th>
                                                            <th class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($moduleCart as $line)
                                                            <tr class="border-b border-slate-200">
                                                                <td class="py-2">{{ $line['item_name'] }}</td>
                                                                <td class="py-2 text-center">
                                                                    <x-ui.input type="number" class="w-20"
                                                                        wire:model.lazy="cart.{{ $module->id }}.{{ $loop->index }}.qty"
                                                                        wire:change="updateCartItem({{ $module->id }}, {{ $line['menu_item_id'] }}, $event.target.value)"
                                                                        min="1" />
                                                                </td>
                                                                <td class="py-2 text-right">
                                                                    {{ number_format($line['unit_price'], 2) }}</td>
                                                                <td class="py-2 text-right">
                                                                    {{ number_format($line['subtotal'], 2) }}</td>
                                                                <td class="py-2 text-center">
                                                                    <x-ui.button type="button" variant="outline-danger"
                                                                        size="sm"
                                                                        wire:click="removeCartItem({{ $module->id }}, {{ $line['menu_item_id'] }})">Remove</x-ui.button>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5"
                                                                    class="py-6 text-center text-gray-500">No items
                                                                    added yet.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="space-y-2">
                                                    <div class="flex justify-between text-sm text-gray-600">
                                                        <span>Subtotal</span>
                                                        <span>{{ number_format($subtotal, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-sm text-gray-600">
                                                        <span>Tax ({{ number_format($taxRate * 100, 2) }}%)</span>
                                                        <span>{{ number_format($tax, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-sm text-gray-600">
                                                        <span>Service Charge</span>
                                                        <span>{{ number_format($serviceCharge, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-sm text-gray-600">
                                                        <span>Discount</span>
                                                        <span>{{ number_format($discount, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-base font-semibold">
                                                        <span>Total</span>
                                                        <span>{{ number_format($total, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-sm text-gray-600">
                                                        <span>Paid</span>
                                                        <span>{{ number_format($paidPreview, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between text-sm font-semibold">
                                                        <span>Balance</span>
                                                        <span>{{ number_format($remaining, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid gap-3">
                                                <div class="panel bg-slate-50 border border-slate-200 p-4">
                                                    <h3 class="font-semibold mb-3">Order settings</h3>
                                                    <div class="grid gap-3">
                                                        <x-ui.select label="Order Type" name="sale_type"
                                                            wire:model="sale_type">
                                                            <option value="dine_in">Dine In</option>
                                                            <option value="takeaway">Takeaway</option>
                                                            <option value="delivery">Delivery</option>
                                                            <option value="online">Online</option>
                                                        </x-ui.select>
                                                        <x-ui.checkbox label="Send order to kitchen"
                                                            name="notifyKitchen" wire:model="notifyKitchen" />
                                                        <x-ui.input label="Notes" name="notes"
                                                            wire:model="notes" />
                                                    </div>
                                                </div>

                                                <div class="panel bg-slate-50 border border-slate-200 p-4">
                                                    <div class="mb-3 flex items-center justify-between gap-3">
                                                        <h3 class="font-semibold">Payment</h3>
                                                        <x-ui.button type="button" size="sm" variant="secondary"
                                                            wire:click="addPaymentRow({{ $module->id }})" icon="plus">Split</x-ui.button>
                                                    </div>
                                                    <div class="grid gap-3">
                                                        @foreach ($splitPayments as $index => $payment)
                                                            <div class="grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-3 md:grid-cols-[1fr_1fr_1fr_auto]">
                                                                <x-ui.select label="Method"
                                                                    name="splitPayments{{ $index }}Method"
                                                                    wire:model.live="splitPayments.{{ $index }}.method"
                                                                    wire:change="autofillPaymentAmount({{ $module->id }}, {{ $index }})">
                                                                    <option value="cash">Cash</option>
                                                                    <option value="card">Card</option>
                                                                    <option value="mobile_money">Mobile money</option>
                                                                    <option value="bank_transfer">Bank transfer</option>
                                                                    <option value="wallet">Wallet</option>
                                                                    <option value="credit_sale">Credit sale</option>
                                                                </x-ui.select>
                                                                <x-ui.input label="Amount" type="number"
                                                                    name="splitPayments{{ $index }}Amount"
                                                                    wire:model.live="splitPayments.{{ $index }}.amount"
                                                                    min="0" step="0.01" />
                                                                <x-ui.input label="Reference"
                                                                    name="splitPayments{{ $index }}Reference"
                                                                    wire:model="splitPayments.{{ $index }}.transaction_reference" />
                                                                <div class="flex items-end">
                                                                    <x-ui.button type="button" variant="outline-danger"
                                                                        size="sm"
                                                                        wire:click="removePaymentRow({{ $index }})">Remove</x-ui.button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                        <x-ui.button wire:click="saveSale({{ $module->id }})"
                                                            icon="shopping-cart">Place Order</x-ui.button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </x-ui.tabs>
            </div>

            <div class="panel bg-slate-50 border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Quick customer registration</h3>
                        <p class="text-sm text-gray-500">Register a customer before reviewing sales history.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input label="Name" name="new_customer_name" wire:model.defer="new_customer_name" />
                    <x-ui.input label="Email" name="new_customer_email" wire:model.defer="new_customer_email" />
                    <x-ui.input label="Phone" name="new_customer_phone" wire:model.defer="new_customer_phone" />
                    <x-ui.input label="Address" name="new_customer_address"
                        wire:model.defer="new_customer_address" />
                </div>
                <div class="mt-4">
                    <x-ui.button wire:click="createCustomer" type="button" icon="user-plus">Register
                        Customer</x-ui.button>
                </div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold">Last 20 sales</h2>
                        <p class="text-sm text-gray-500">Recent orders for this branch and your assigned POS modules.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>Sale #</th>
                                <th>Customer</th>
                                <th>Module</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lastSales as $sale)
                                <tr>
                                    <td>{{ $sale->sale_number }}</td>
                                    <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                    <td>{{ $sale->module?->name }}</td>
                                    <td>{{ number_format($sale->total, 2) }}</td>
                                    <td>{{ ucfirst($sale->status) }}</td>
                                    <td>{{ $sale->sale_date?->format('Y-m-d H:i') }}</td>
                                    <td class="text-center">
                                        <x-ui.button type="button" size="sm" variant="secondary"
                                            wire:click="viewReceipt({{ $sale->id }})" icon="eye">View</x-ui.button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-10 text-gray-500">No sales yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>
            </div>
        @endif

        <div x-data="{
            printReceipt() {
                const receipt = document.getElementById('pos-receipt-print-area');
                if (!receipt) return;

                const printWindow = window.open('', 'posReceiptPrint', 'width=420,height=720');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Receipt</title>
                            <style>
                                body { margin: 0; background: #fff; color: #111827; font-family: Arial, sans-serif; }
                                .receipt-shell { width: 80mm; margin: 0 auto; padding: 16px; }
                                table { width: 100%; border-collapse: collapse; }
                                th, td { padding: 6px 0; }
                                .text-center { text-align: center; }
                                .text-right { text-align: right; }
                                .border-b { border-bottom: 1px solid #e5e7eb; }
                                .border-dashed { border-bottom-style: dashed; }
                                .font-semibold { font-weight: 600; }
                                .font-extrabold { font-weight: 800; }
                                .text-sm { font-size: 12px; }
                                .text-xs { font-size: 11px; }
                                .space-y-2 > * + * { margin-top: 8px; }
                                .space-y-5 > * + * { margin-top: 20px; }
                                .flex { display: flex; }
                                .justify-between { justify-content: space-between; }
                            </style>
                        </head>
                        <body>
                            <div class='receipt-shell'>${receipt.innerHTML}</div>
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => printWindow.print(), 250);
            }
        }">
        <x-ui.modal name="pos-receipt-modal" maxWidth="2xl">
            <x-slot:title>Sale Receipt</x-slot:title>

            @if ($receiptSale)
                <div id="pos-receipt-print-area" class="space-y-5">
                    <div class="border-b border-dashed border-slate-300 pb-4 text-center">
                        <h2 class="text-xl font-extrabold">{{ $receiptSettings['business_name'] }}</h2>
                        @if ($receiptSettings['tagline'])
                            <p class="text-sm text-gray-500">{{ $receiptSettings['tagline'] }}</p>
                        @endif
                        @if ($receiptSettings['address'])
                            <p class="text-xs text-gray-500">{{ $receiptSettings['address'] }}</p>
                        @endif
                        <p class="text-xs text-gray-500">
                            {{ collect([$receiptSettings['phone'], $receiptSettings['email']])->filter()->implode(' · ') }}
                        </p>
                        @if ($receiptSettings['whatsapp'])
                            <p class="text-xs text-gray-500">WhatsApp: {{ $receiptSettings['whatsapp'] }}</p>
                        @endif
                        <p class="mt-2 text-sm text-gray-500">{{ $receiptSale->module?->name }} POS Receipt</p>
                        <p class="mt-2 text-sm font-semibold">{{ $receiptSale->sale_number }}</p>
                        <p class="text-xs text-gray-500">{{ $receiptSale->sale_date?->format('M d, Y h:i A') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-gray-500">Customer</p>
                            <p class="font-semibold">{{ $receiptSale->customer?->name ?? 'Walk-in Customer' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-gray-500">Cashier</p>
                            <p class="font-semibold">{{ $receiptSale->creator?->name ?? 'System' }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-gray-500">
                                    <th class="py-2">Item</th>
                                    <th class="py-2 text-center">Qty</th>
                                    <th class="py-2 text-right">Price</th>
                                    <th class="py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($receiptSale->items as $item)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2">{{ $item->item_name }}</td>
                                        <td class="py-2 text-center">{{ number_format($item->qty, 0) }}</td>
                                        <td class="py-2 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="py-2 text-right">{{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-2 border-b border-dashed border-slate-300 pb-4 text-sm">
                        <div class="flex justify-between"><span>Subtotal</span><span>{{ number_format($receiptSale->subtotal, 2) }}</span></div>
                        <div class="flex justify-between"><span>Tax</span><span>{{ number_format($receiptSale->tax, 2) }}</span></div>
                        <div class="flex justify-between"><span>Service Charge</span><span>{{ number_format($receiptSale->service_charge, 2) }}</span></div>
                        <div class="flex justify-between"><span>Discount</span><span>{{ number_format($receiptSale->discount, 2) }}</span></div>
                        <div class="flex justify-between text-base font-extrabold"><span>Total</span><span>{{ number_format($receiptSale->total, 2) }}</span></div>
                        <div class="flex justify-between"><span>Paid</span><span>{{ number_format($receiptSale->paid_amount, 2) }}</span></div>
                        <div class="flex justify-between"><span>Balance</span><span>{{ number_format($receiptSale->remaining_balance, 2) }}</span></div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <p class="font-semibold">Payments</p>
                        @forelse ($receiptSale->payments as $payment)
                            <div class="flex justify-between gap-4">
                                <span>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}
                                    @if ($payment->transaction_reference)
                                        <span class="text-gray-400">({{ $payment->transaction_reference }})</span>
                                    @endif
                                </span>
                                <span>{{ number_format($payment->amount, 2) }}</span>
                            </div>
                        @empty
                            <p class="text-gray-500">No payment recorded.</p>
                        @endforelse
                    </div>

                    <p class="pt-2 text-center text-xs text-gray-500">Thank you for your visit.</p>
                </div>
            @else
                <p class="text-gray-500">Receipt is not available.</p>
            @endif

            <x-slot:footer>
                <div class="flex flex-wrap justify-end gap-3">
                    <x-ui.button type="button" variant="secondary" icon="printer"
                        x-on:click="printReceipt()">Print</x-ui.button>
                    <x-ui.button type="button" icon="arrow-down-tray"
                        x-on:click="printReceipt()">Download PDF</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
        </div>
    </div>
</div>
