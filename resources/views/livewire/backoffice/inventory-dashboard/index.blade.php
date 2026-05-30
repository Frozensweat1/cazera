<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Inventory Dashboard</h1>
                <p class="text-gray-500">Overview of inventory levels, locations, and recent activity.</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <x-ui.card>
                <div class="text-sm text-gray-500">Total Items</div>
                <div class="mt-3 text-3xl font-bold">{{ $totalItems }}</div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-sm text-gray-500">Active Items</div>
                <div class="mt-3 text-3xl font-bold">{{ $activeItems }}</div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-sm text-gray-500">Locations</div>
                <div class="mt-3 text-3xl font-bold">{{ $totalLocations }}</div>
            </x-ui.card>
            <x-ui.card>
                <div class="text-sm text-gray-500">Active Locations</div>
                <div class="mt-3 text-3xl font-bold">{{ $activeLocations }}</div>
            </x-ui.card>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <div class="panel">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Low Stock Items</h2>
                    <span class="text-sm text-gray-500">Needs attention</span>
                </div>
                @if ($lowStockItems->isEmpty())
                    <div class="text-center py-10 text-gray-500">No low stock items.</div>
                @else
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>On Hand</th>
                                <th>Reorder</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowStockItems as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ number_format($item->quantity_on_hand, 2) }}</td>
                                    <td>{{ number_format($item->reorder_level, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                @endif
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Recent Activity</h2>
                    <span class="text-sm text-gray-500">Last 5 records</span>
                </div>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-600">Adjustments</h3>
                        @if ($recentAdjustments->isEmpty())
                            <div class="py-6 text-center text-gray-500">No recent adjustments.</div>
                        @else
                            <x-ui.table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Change</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentAdjustments as $adjustment)
                                        <tr>
                                            <td>{{ $adjustment->inventoryItem?->name }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $adjustment->type)) }}</td>
                                            <td>{{ number_format($adjustment->change_qty, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </x-ui.table>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-600">Transfers</h3>
                        @if ($recentTransfers->isEmpty())
                            <div class="py-6 text-center text-gray-500">No recent transfers.</div>
                        @else
                            <x-ui.table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>From</th>
                                        <th>To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentTransfers as $transfer)
                                        <tr>
                                            <td>{{ $transfer->inventoryItem?->name }}</td>
                                            <td>{{ $transfer->fromBranch?->name }}</td>
                                            <td>{{ $transfer->toBranch?->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </x-ui.table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
