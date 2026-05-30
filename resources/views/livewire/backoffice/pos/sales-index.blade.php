<div x-data="{
    printReceipt() {
        const receipt = document.getElementById('sales-receipt-print-area');
        if (! receipt) return;

        const printWindow = window.open('', 'salesReceiptPrint', 'width=420,height=720');
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
                <body><div class='receipt-shell'>${receipt.innerHTML}</div></body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => printWindow.print(), 250);
    }
}">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Sales List</h1>
                <p class="text-gray-500">Browse POS sales records, collect balances, print receipts, and process refunds.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search sales..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterModule" wire:model.live="filterModule">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterStatus" wire:model.live="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cooking">Cooking</option>
                    <option value="ready">Ready</option>
                    <option value="served">Served</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Sale #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Remaining</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sale->sale_number }}</td>
                            <td>{{ $sale->sale_date->format('Y-m-d H:i') }}</td>
                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td>
                                @php
                                    $statusClass = match ($sale->status) {
                                        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'served', 'ready' => 'bg-sky-50 text-sky-700 ring-sky-200',
                                        'cooking', 'confirmed' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                        'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
                                        default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $sale->status)) }}
                                </span>
                            </td>
                            <td>{{ number_format($sale->total, 2) }}</td>
                            <td>{{ number_format($sale->paid_amount, 2) }}</td>
                            <td>
                                <span @class([
                                    'font-semibold',
                                    'text-amber-600' => (float) $sale->remaining_balance > 0,
                                    'text-emerald-600' => (float) $sale->remaining_balance <= 0,
                                ])>
                                    {{ number_format($sale->remaining_balance, 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="eye" wire:click="viewReceipt({{ $sale->id }})">View</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item icon="printer" wire:click="viewReceipt({{ $sale->id }})"
                                        x-on:click="$nextTick(() => setTimeout(() => printReceipt(), 650))">Print</x-ui.table-dropdown-item>
                                    @if ((float) $sale->remaining_balance > 0 && $sale->status !== 'refunded')
                                        <x-ui.table-dropdown-item icon="banknotes" wire:click="openPayment({{ $sale->id }})">Payment</x-ui.table-dropdown-item>
                                    @endif
                                    @if ((float) $sale->paid_amount > 0 && $sale->status !== 'refunded')
                                        <x-ui.table-dropdown-item danger icon="arrow-uturn-left" wire:click="openRefund({{ $sale->id }})">Refund</x-ui.table-dropdown-item>
                                    @endif
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-500">No sales found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $sales->links() }}</div>
        </div>

        <x-ui.modal name="sales-payment-modal" maxWidth="xl">
            <x-slot:title>Record Balance Payment</x-slot:title>
            @if ($paymentSale)
                <div class="space-y-4">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="font-semibold">{{ $paymentSale->sale_number }}</p>
                        <p class="text-sm text-gray-500">Outstanding: {{ number_format($paymentSale->remaining_balance, 2) }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-ui.select label="Method" name="payment_method" wire:model="payment_method">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_money">Mobile money</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="wallet">Wallet</option>
                        </x-ui.select>
                        <x-ui.input label="Amount" type="number" name="payment_amount" wire:model="payment_amount" min="0" step="0.01" />
                        <x-ui.input label="Reference" name="payment_reference" wire:model="payment_reference" />
                    </div>
                </div>
            @endif
            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'sales-payment-modal')">Cancel</x-ui.button>
                    <x-ui.button type="button" wire:click="collectPayment" icon="check">Save Payment</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        <x-ui.modal name="sales-refund-modal" maxWidth="xl">
            <x-slot:title>Process Refund</x-slot:title>
            @if ($refundSale)
                <div class="space-y-4">
                    <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                        <p class="font-semibold text-red-700">{{ $refundSale->sale_number }}</p>
                        <p class="text-sm text-red-600">Paid amount: {{ number_format($refundSale->paid_amount, 2) }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.select label="Refund Method" name="refund_method" wire:model="refund_method">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_money">Mobile money</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="wallet">Wallet</option>
                        </x-ui.select>
                        <x-ui.input label="Refund Amount" type="number" name="refund_amount" wire:model="refund_amount" min="0" step="0.01" />
                    </div>
                    <x-ui.textarea label="Reason" name="refund_reason" wire:model="refund_reason" />
                </div>
            @endif
            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'sales-refund-modal')">Cancel</x-ui.button>
                    <x-ui.button type="button" variant="danger" wire:click="processRefund" icon="arrow-uturn-left">Process Refund</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        <x-ui.modal name="sales-receipt-modal" maxWidth="2xl">
            <x-slot:title>Sale Receipt</x-slot:title>
            @if ($receiptSale)
                <div id="sales-receipt-print-area" class="space-y-5">
                    <div class="border-b border-dashed border-slate-300 pb-4 text-center">
                        <h2 class="text-xl font-extrabold">{{ $receiptSettings['business_name'] }}</h2>
                        @if ($receiptSettings['tagline'])
                            <p class="text-sm text-gray-500">{{ $receiptSettings['tagline'] }}</p>
                        @endif
                        @if ($receiptSettings['address'])
                            <p class="text-xs text-gray-500">{{ $receiptSettings['address'] }}</p>
                        @endif
                        <p class="text-xs text-gray-500">{{ collect([$receiptSettings['phone'], $receiptSettings['email']])->filter()->implode(' · ') }}</p>
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
                </div>
            @endif
            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="secondary" icon="printer" x-on:click="printReceipt()">Print</x-ui.button>
                    <x-ui.button type="button" icon="arrow-down-tray" x-on:click="printReceipt()">Download PDF</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    </div>
</div>
