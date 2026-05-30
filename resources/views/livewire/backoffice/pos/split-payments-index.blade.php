<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Split Payments</h1>
                <p class="text-gray-500">Sales using more than one payment method.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search split payments..." />
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
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Sale #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payments</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesWithSplits as $sale)
                        <tr>
                            <td>{{ $sale->sale_number }}</td>
                            <td>{{ $sale->sale_date->format('Y-m-d H:i') }}</td>
                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td>{{ number_format($sale->total, 2) }}</td>
                            <td>{{ $sale->payments->count() }}</td>
                            <td>{{ $sale->branch?->name }}</td>
                            <td>{{ $sale->module?->name }}</td>
                            <td class="text-right">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="eye" wire:click="viewPayments({{ $sale->id }})">
                                        View Payments
                                    </x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-500">No split payment sales found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $salesWithSplits->links() }}</div>
        </div>

        <x-ui.modal name="split-payments-view-modal" maxWidth="3xl">
            <x-slot:title>Split Payment Details</x-slot:title>

            @if ($viewSale)
                <div class="space-y-5">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Sale</p>
                                <p class="font-semibold text-gray-900">{{ $viewSale->sale_number }}</p>
                                <p class="text-xs text-gray-500">{{ $viewSale->sale_date?->format('M d, Y h:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Customer</p>
                                <p class="font-semibold text-gray-900">{{ $viewSale->customer?->name ?? 'Walk-in Customer' }}</p>
                                <p class="text-xs text-gray-500">{{ $viewSale->creator?->name ? 'Cashier: ' . $viewSale->creator->name : 'Cashier: System' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Branch / Module</p>
                                <p class="font-semibold text-gray-900">{{ $viewSale->branch?->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $viewSale->module?->name ?? 'No module' }}</p>
                            </div>
                            <div class="md:text-right">
                                <p class="text-xs uppercase tracking-wide text-gray-500">Total Paid</p>
                                <p class="text-xl font-extrabold text-gray-950">{{ number_format($viewSale->payments->sum('amount'), 2) }}</p>
                                <p class="text-xs text-gray-500">Sale total: {{ number_format($viewSale->total, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Method</th>
                                    <th class="px-4 py-3">Reference</th>
                                    <th class="px-4 py-3">Received By</th>
                                    <th class="px-4 py-3">Paid At</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($viewSale->payments as $payment)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $payment->transaction_reference ?: 'N/A' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $payment->receiver?->name ?? 'System' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $payment->paid_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $payment->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-slate-200 bg-slate-50">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-right font-semibold text-gray-700">Payment Total</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-gray-950">{{ number_format($viewSale->payments->sum('amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            <x-slot:footer>
                <div class="flex justify-end">
                    <x-ui.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'split-payments-view-modal')">
                        Close
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    </div>
</div>
