<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Stock Movement Report</h1>
                <p class="text-gray-500">Review inventory adjustments and branch transfers in one place.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search"
                    placeholder="Search movements by item, reference or reason..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterType" wire:model.live="filterType">
                    <option value="all">All Movements</option>
                    <option value="adjustment">Adjustments</option>
                    <option value="transfer">Transfers</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Destination</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr>
                            <td>{{ $movement->date?->format('Y-m-d') }}</td>
                            <td>
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ $movement->item_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $movement->reference }}</p>
                                </div>
                            </td>
                            <td>{{ $movement->movement_type }}</td>
                            <td>{{ $movement->source }}</td>
                            <td>{{ $movement->destination }}</td>
                            <td>{{ number_format($movement->quantity, 2) }}</td>
                            <td>{{ $movement->status ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-gray-500">No stock movements found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $movements->links() }}</div>
        </div>
    </div>
</div>
