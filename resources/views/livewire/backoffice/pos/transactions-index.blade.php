<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Cash Register Control</h1>
                <p class="text-gray-500">Compare expected collections against actual handovers and review POS register activity.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <x-ui.input type="date" name="filterDate" wire:model.live="filterDate" />
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search register, sale, notes..." />
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
                <x-ui.select name="filterType" wire:model.live="filterType">
                    <option value="">All Types</option>
                    <option value="sale">Sale</option>
                    <option value="refund">Refund</option>
                    <option value="expense">Expense</option>
                    <option value="cash_in">Cash In</option>
                    <option value="cash_out">Cash Out</option>
                </x-ui.select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="panel">
                <p class="text-sm text-gray-500">Registers</p>
                <p class="mt-2 text-2xl font-extrabold text-gray-950">{{ number_format($summary['registers']) }}</p>
                <p class="text-xs text-gray-500">{{ number_format($summary['open_registers']) }} open</p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Expected</p>
                <p class="mt-2 text-2xl font-extrabold text-gray-950">{{ number_format($summary['expected_total'], 2) }}</p>
                <p class="text-xs text-gray-500">Sales plus cash-ins less refunds</p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Actual Received</p>
                <p class="mt-2 text-2xl font-extrabold text-gray-950">{{ number_format($summary['actual_total'], 2) }}</p>
                <p class="text-xs text-gray-500">Closed registers only</p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Difference</p>
                <p @class([
                    'mt-2 text-2xl font-extrabold',
                    'text-emerald-700' => $summary['difference_total'] > 0,
                    'text-red-700' => $summary['difference_total'] < 0,
                    'text-gray-950' => $summary['difference_total'] == 0,
                ])>{{ number_format($summary['difference_total'], 2) }}</p>
                <p class="text-xs text-gray-500">Actual minus expected</p>
            </div>
            <div class="panel">
                <p class="text-sm text-gray-500">Sales / Refunds</p>
                <p class="mt-2 text-2xl font-extrabold text-gray-950">{{ number_format($summary['sale_collections'], 2) }}</p>
                <p class="text-xs text-gray-500">Refunds: {{ number_format($summary['refunds'], 2) }}</p>
            </div>
        </div>

        <div class="panel">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-950">Register History</h2>
                    <p class="text-sm text-gray-500">Expected collection is calculated from sales, refunds, cash-ins, and opening balance entries.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                @forelse ($registers as $register)
                    @php
                        $expected = (float) $register->computed_expected_collection;
                        $actual = $register->computed_actual_collection;
                        $difference = (float) $register->computed_difference;
                    @endphp
                    <div class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-gray-950">{{ $register->name ?: 'POS Register' }}</h3>
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-200' => $register->is_open,
                                        'bg-slate-100 text-slate-700 ring-slate-200' => ! $register->is_open,
                                    ])>{{ $register->is_open ? 'Open' : 'Closed' }}</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ $register->branch?->name }}{{ $register->module ? ' / ' . $register->module->name : '' }}</p>
                                <p class="text-xs text-gray-500">
                                    Opened by {{ $register->openedBy?->name ?? 'System' }} at {{ $register->opened_at?->format('M d, Y h:i A') }}
                                </p>
                                @if (! $register->is_open)
                                    <p class="text-xs text-gray-500">
                                        Closed by {{ $register->closedBy?->name ?? 'System' }} at {{ $register->closed_at?->format('M d, Y h:i A') }}
                                    </p>
                                @endif
                                <p class="text-xs text-gray-500">
                                    Transactions: {{ number_format($register->transactions->count()) }}
                                </p>
                            </div>

                            @if ($register->is_open)
                                <x-ui.button type="button" icon="lock-closed" wire:click="openCloseRegister({{ $register->id }})">
                                    Close
                                </x-ui.button>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-xs text-gray-500">Expected</p>
                                <p class="mt-1 font-extrabold text-gray-950">{{ number_format($expected, 2) }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-xs text-gray-500">Actual</p>
                                <p class="mt-1 font-extrabold text-gray-950">{{ $register->is_open ? 'Pending' : number_format($actual, 2) }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-xs text-gray-500">Difference</p>
                                <p @class([
                                    'mt-1 font-extrabold',
                                    'text-emerald-700' => ! $register->is_open && $difference > 0,
                                    'text-red-700' => ! $register->is_open && $difference < 0,
                                    'text-gray-950' => $register->is_open || $difference == 0,
                                ])>{{ $register->is_open ? 'Pending' : number_format($difference, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-lg border border-dashed border-slate-300 p-8 text-center text-gray-500">
                        No register periods found for the selected filters.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-950">Transaction Ledger</h2>
                <p class="text-sm text-gray-500">Detailed register activity for the selected day.</p>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Sale #</th>
                        <th>Amount</th>
                        <th>Cash Register</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th>Performed By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                            <td>
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1',
                                    'bg-emerald-50 text-emerald-700 ring-emerald-200' => $transaction->type === 'sale' || $transaction->type === 'cash_in',
                                    'bg-red-50 text-red-700 ring-red-200' => $transaction->type === 'refund' || $transaction->type === 'cash_out' || $transaction->type === 'expense',
                                    'bg-slate-100 text-slate-700 ring-slate-200' => ! in_array($transaction->type, ['sale', 'cash_in', 'refund', 'cash_out', 'expense'], true),
                                ])>{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</span>
                            </td>
                            <td>{{ $transaction->sale?->sale_number ?? 'N/A' }}</td>
                            <td @class([
                                'font-semibold',
                                'text-red-700' => (float) $transaction->amount < 0,
                                'text-gray-950' => (float) $transaction->amount >= 0,
                            ])>{{ number_format($transaction->amount, 2) }}</td>
                            <td>{{ $transaction->cashRegister?->name ?? 'Unknown' }}</td>
                            <td>{{ $transaction->branch?->name }}</td>
                            <td>{{ $transaction->module?->name }}</td>
                            <td>{{ $transaction->performer?->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $transactions->links() }}</div>
        </div>

        <x-ui.modal name="cash-register-close-modal" maxWidth="xl">
            <x-slot:title>Close Cash Register</x-slot:title>

            @if ($closingRegister)
                <div class="space-y-5">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="font-bold text-gray-950">{{ $closingRegister->name ?: 'POS Register' }}</p>
                        <p class="text-sm text-gray-500">{{ $closingRegister->branch?->name }}{{ $closingRegister->module ? ' / ' . $closingRegister->module->name : '' }}</p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Expected</p>
                                <p class="text-xl font-extrabold text-gray-950">{{ number_format($closingRegister->computed_expected_collection, 2) }}</p>
                                <p class="mt-1 text-xs text-gray-500">Sales, refunds, cash-ins, and opening balance entries count toward expected collection.</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Opened By</p>
                                <p class="font-semibold text-gray-950">{{ $closingRegister->openedBy?->name ?? 'System' }}</p>
                            </div>
                        </div>
                    </div>

                    <x-ui.input label="Actual Cash Received" type="number" name="actual_balance" wire:model="actual_balance" min="0" step="0.01" />
                    <x-ui.textarea label="Closing Notes" name="closing_notes" wire:model="closing_notes" placeholder="Optional handover notes, shortage reason, or overage details." />
                </div>
            @endif

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'cash-register-close-modal')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="button" icon="check" wire:click="closeRegister" wire:target="closeRegister" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="closeRegister">Close Register</span>
                        <span wire:loading wire:target="closeRegister">Closing...</span>
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    </div>
</div>
