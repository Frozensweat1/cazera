<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Customer History</h1>
                <p class="text-gray-500">View customer activity across every branch.</p>
            </div>

            <a href="{{ route('backoffice.customers') }}" class="btn btn-outline-primary inline-flex items-center gap-2">
                <x-heroicon-o-users class="h-4 w-4" />
                Customers
            </a>
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.9fr_1.4fr]">
            <div class="panel space-y-5">
                <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-1 2xl:grid-cols-3">
                    <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search customers..." />

                    <x-ui.select name="customerType" wire:model.live="customerType">
                        <option value="">All Types</option>
                        <option value="regular">Regular</option>
                        <option value="vip">VIP</option>
                        <option value="corporate">Corporate</option>
                    </x-ui.select>

                    <x-ui.select name="status" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="banned">Banned</option>
                    </x-ui.select>
                </div>

                <div class="space-y-3">
                    @forelse ($customers as $customer)
                        <button type="button" wire:key="customer-history-{{ $customer->id }}"
                            wire:click="selectCustomer({{ $customer->id }})"
                            @class([
                                'w-full rounded-xl border p-4 text-left transition hover:border-primary hover:bg-primary/5',
                                'border-primary bg-primary/10' => $selectedCustomerId === $customer->id,
                                'border-gray-200 bg-white dark:border-[#25314a] dark:bg-[#0e1726]' => $selectedCustomerId !== $customer->id,
                            ])>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $customer->name }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $customer->phone ?: $customer->email ?: 'No contact' }}</p>
                                    <p class="mt-2 text-xs text-gray-500">{{ $customer->branch?->name ?? 'No branch' }}</p>
                                </div>
                                <span @class([
                                    'badge shrink-0',
                                    'bg-warning' => $customer->customer_type === 'vip',
                                    'bg-primary' => $customer->customer_type === 'corporate',
                                    'bg-secondary' => $customer->customer_type === 'regular',
                                ])>
                                    {{ ucfirst($customer->customer_type) }}
                                </span>
                            </div>
                        </button>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-gray-500">
                            No customers found.
                        </div>
                    @endforelse
                </div>

                {{ $customers->links() }}
            </div>

            <div class="space-y-6">
                @if ($selectedCustomer)
                    <div class="panel">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wide text-primary">Selected Customer</p>
                                <h2 class="mt-1 text-2xl font-bold">{{ $selectedCustomer->name }}</h2>
                                <p class="mt-2 text-gray-500">
                                    {{ $selectedCustomer->phone ?: 'No phone' }}
                                    @if ($selectedCustomer->email)
                                        <span class="mx-2 text-gray-300">|</span>{{ $selectedCustomer->email }}
                                    @endif
                                </p>
                                <p class="mt-1 text-sm text-gray-500">Registered from {{ $selectedCustomer->branch?->name ?? 'No branch' }}</p>
                            </div>

                            <span @class([
                                'badge',
                                'bg-success' => $selectedCustomer->status === 'active',
                                'bg-warning' => $selectedCustomer->status === 'inactive',
                                'bg-danger' => $selectedCustomer->status === 'banned',
                            ])>
                                {{ ucfirst($selectedCustomer->status) }}
                            </span>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-xl bg-primary-light p-4 text-primary">
                                <p class="text-xs font-bold uppercase">Orders</p>
                                <p class="mt-2 text-2xl font-bold">{{ number_format($historyTotals['orders']) }}</p>
                            </div>
                            <div class="rounded-xl bg-success-light p-4 text-success">
                                <p class="text-xs font-bold uppercase">Sales</p>
                                <p class="mt-2 text-2xl font-bold">{{ number_format($historyTotals['sales'], 2) }}</p>
                            </div>
                            <div class="rounded-xl bg-info-light p-4 text-info">
                                <p class="text-xs font-bold uppercase">Paid</p>
                                <p class="mt-2 text-2xl font-bold">{{ number_format($historyTotals['paid'], 2) }}</p>
                            </div>
                            <div class="rounded-xl bg-danger-light p-4 text-danger">
                                <p class="text-xs font-bold uppercase">Debt</p>
                                <p class="mt-2 text-2xl font-bold">{{ number_format($historyTotals['debt'], 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="panel space-y-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <h3 class="text-xl font-bold">Sales History</h3>
                                <p class="text-sm text-gray-500">Transactions are shown across all branches.</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-4">
                                <x-ui.input label="From" type="date" name="dateFrom" wire:model.live="dateFrom" />
                                <x-ui.input label="To" type="date" name="dateTo" wire:model.live="dateTo" />
                                <x-ui.select label="Status" name="saleStatus" wire:model.live="saleStatus">
                                    <option value="">All</option>
                                    <option value="draft">Draft</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="refunded">Refunded</option>
                                </x-ui.select>
                                <div class="flex items-end">
                                    <x-ui.button variant="outline-danger" wire:click="clearHistoryFilters" target="clearHistoryFilters">
                                        Clear
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>

                        <x-ui.table>
                            <thead>
                                <tr>
                                    <th>Sale</th>
                                    <th>Branch</th>
                                    <th>Module</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr>
                                        <td>
                                            <p class="font-semibold">{{ $sale->sale_number }}</p>
                                            <p class="text-xs text-gray-500">{{ $sale->items->count() }} item(s)</p>
                                        </td>
                                        <td>{{ $sale->branch?->name ?? '-' }}</td>
                                        <td>{{ $sale->module?->name ?? '-' }}</td>
                                        <td>
                                            <span @class([
                                                'badge',
                                                'bg-success' => $sale->status === 'completed',
                                                'bg-warning' => $sale->status === 'draft',
                                                'bg-danger' => in_array($sale->status, ['cancelled', 'refunded'], true),
                                                'bg-secondary' => ! in_array($sale->status, ['completed', 'draft', 'cancelled', 'refunded'], true),
                                            ])>
                                                {{ ucfirst($sale->status) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($sale->total, 2) }}</td>
                                        <td>{{ number_format($sale->paid_amount, 2) }}</td>
                                        <td>
                                            <span @class([
                                                'font-semibold',
                                                'text-success' => $sale->remaining_balance <= 0,
                                                'text-danger' => $sale->remaining_balance > 0,
                                            ])>
                                                {{ number_format($sale->remaining_balance, 2) }}
                                            </span>
                                        </td>
                                        <td>{{ $sale->sale_date?->format('M d, Y h:i A') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-10 text-center text-gray-500">
                                            No sales history found for this customer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-ui.table>

                        {{ $sales->links() }}
                    </div>
                @else
                    <div class="panel py-16 text-center">
                        <div class="mx-auto grid h-14 w-14 place-content-center rounded-full bg-primary-light text-primary">
                            <x-heroicon-o-clock class="h-7 w-7" />
                        </div>
                        <h2 class="mt-4 text-xl font-bold">Select a customer</h2>
                        <p class="mt-2 text-gray-500">Choose any customer to view their full cross-branch history.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
