<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Cash Register Report</h2>
                <p class="text-sm text-gray-500">Register accountability, expected versus actual collections, refunds, and cash movements.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <x-ui.input type="date" name="dateFrom" wire:model.live="dateFrom" />
                <x-ui.input type="date" name="dateTo" wire:model.live="dateTo" />
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
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="panel"><p class="text-sm text-gray-500">Transactions</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($totalTransactions) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Sale Collections</p><p class="mt-2 text-2xl font-extrabold text-emerald-700">{{ number_format($saleCollections, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Refunds</p><p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($refunds, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Expected</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($expectedTotal, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Actual Closed</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($actualTotal, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Variance</p><p @class(['mt-2 text-2xl font-extrabold', 'text-emerald-700' => $differenceTotal >= 0, 'text-red-700' => $differenceTotal < 0])>{{ number_format($differenceTotal, 2) }}</p></div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Register History</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($registerBalances as $register)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <div class="flex justify-between gap-4"><strong>{{ $register->name ?: 'POS Register' }}</strong><span>{{ $register->is_open ? 'Open' : 'Closed' }}</span></div>
                            <p class="text-xs text-gray-500">{{ $register->branch?->name }} / {{ $register->module?->name ?? 'No module' }}</p>
                            <div class="mt-2 grid grid-cols-3 gap-2 text-sm">
                                <div><span class="text-gray-500">Expected</span><p class="font-bold">{{ number_format($register->expected_balance, 2) }}</p></div>
                                <div><span class="text-gray-500">Actual</span><p class="font-bold">{{ $register->is_open ? 'Pending' : number_format($register->actual_balance, 2) }}</p></div>
                                <div><span class="text-gray-500">Diff</span><p @class(['font-bold', 'text-emerald-700' => (float) $register->difference >= 0, 'text-red-700' => (float) $register->difference < 0])>{{ $register->is_open ? 'Pending' : number_format($register->difference, 2) }}</p></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No registers found for this period.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Transaction Type Breakdown</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex justify-between rounded-lg bg-slate-50 p-3"><span>Cash In</span><strong>{{ number_format($cashIn, 2) }}</strong></div>
                    <div class="flex justify-between rounded-lg bg-slate-50 p-3"><span>Cash Out</span><strong>{{ number_format($cashOut, 2) }}</strong></div>
                    @forelse ($transactionTypes as $type)
                        <div class="rounded-lg border border-slate-200 p-3"><div class="flex justify-between"><span class="font-semibold">{{ ucwords(str_replace('_', ' ', $type->type)) }}</span><strong>{{ number_format($type->total_amount, 2) }}</strong></div><p class="text-xs text-gray-500">Transactions {{ number_format($type->count) }}</p></div>
                    @empty
                        <p class="text-sm text-gray-500">No transaction breakdown available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel">
            <h3 class="text-lg font-bold text-gray-900">Recent Register Transactions</h3>
            <div class="mt-4 space-y-3">
                @forelse ($recentTransactions as $transaction)
                    <div class="rounded-lg border border-slate-200 p-3"><div class="flex justify-between gap-4"><div><p class="font-semibold">{{ $transaction->cashRegister?->name ?? 'Register' }}</p><p class="text-xs text-gray-500">{{ $transaction->branch?->name ?? 'Branch' }} / {{ ucwords(str_replace('_', ' ', $transaction->type)) }} / {{ $transaction->transaction_date?->format('M d, Y H:i') }}</p></div><strong @class(['text-red-700' => (float) $transaction->amount < 0])>{{ number_format($transaction->amount, 2) }}</strong></div></div>
                @empty
                    <p class="text-sm text-gray-500">No recent register transactions.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
