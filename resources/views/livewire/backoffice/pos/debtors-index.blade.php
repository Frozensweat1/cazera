<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Debtors</h1>
                <p class="text-gray-500">Sales with outstanding balances.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search debtors..." />
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
                        <th>Remaining</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($debtors as $debtor)
                        <tr>
                            <td>{{ $debtor->sale_number }}</td>
                            <td>{{ $debtor->sale_date->format('Y-m-d H:i') }}</td>
                            <td>{{ $debtor->customer?->name ?? 'Walk-in' }}</td>
                            <td>{{ number_format($debtor->total, 2) }}</td>
                            <td>{{ number_format($debtor->remaining_balance, 2) }}</td>
                            <td>{{ $debtor->branch?->name }}</td>
                            <td>{{ $debtor->module?->name }}</td>
                            <td class="text-right">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="banknotes" wire:click="openPayment({{ $debtor->id }})">
                                        Make Payment
                                    </x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-500">No debtor records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $debtors->links() }}</div>
        </div>

        <x-ui.modal name="debtor-payment-modal" maxWidth="xl">
            <x-slot:title>Record Debtor Payment</x-slot:title>

            @if ($paymentSale)
                <div class="space-y-5">
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="font-semibold text-amber-950">{{ $paymentSale->sale_number }}</p>
                                <p class="text-sm text-amber-700">{{ $paymentSale->customer?->name ?? 'Walk-in Customer' }}</p>
                                <p class="text-xs text-amber-700">{{ $paymentSale->branch?->name }}{{ $paymentSale->module ? ' / ' . $paymentSale->module->name : '' }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-xs uppercase tracking-wide text-amber-700">Outstanding</p>
                                <p class="text-xl font-extrabold text-amber-950">{{ number_format($paymentSale->remaining_balance, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <x-ui.select label="Method" name="payment_method" wire:model="payment_method">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile_money">Mobile money</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="wallet">Wallet</option>
                        </x-ui.select>
                        <x-ui.input label="Amount" type="number" name="payment_amount" wire:model="payment_amount" min="0.01" step="0.01" />
                        <x-ui.input label="Reference" name="payment_reference" wire:model="payment_reference" placeholder="Optional" />
                    </div>
                </div>
            @endif

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'debtor-payment-modal')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="collectPayment" wire:target="collectPayment" wire:loading.attr="disabled" icon="check">
                        <span wire:loading.remove wire:target="collectPayment">Save Payment</span>
                        <span wire:loading wire:target="collectPayment">Saving...</span>
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    </div>
</div>
