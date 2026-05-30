<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Inventory Report</h2>
                <p class="text-sm text-gray-500">Stock value, reorder exposure, supplier concentration, and availability risk.</p>
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
            <div class="panel"><p class="text-sm text-gray-500">Items</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($totalItems) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Active / Inactive</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($activeItems) }}</p><p class="text-xs text-gray-500">Inactive {{ number_format($inactiveItems) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Stock Value</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($inventoryValue, 2) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Low Stock</p><p class="mt-2 text-2xl font-extrabold text-amber-700">{{ number_format($lowStockCount) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Stockouts</p><p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($stockoutCount) }}</p></div>
            <div class="panel"><p class="text-sm text-gray-500">Reorder Exposure</p><p class="mt-2 text-2xl font-extrabold">{{ number_format($reorderExposure, 2) }}</p></div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Low Stock Alerts</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($lowStockItems as $item)
                        <div class="rounded-lg border border-slate-200 p-3"><div class="flex justify-between"><strong>{{ $item->name }}</strong><span class="text-red-700">{{ number_format($item->quantity_on_hand, 2) }}</span></div><p class="text-xs text-gray-500">Reorder at {{ number_format($item->reorder_level, 2) }}</p></div>
                    @empty
                        <p class="text-sm text-gray-500">No items below reorder level.</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Highest Value Stock</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($highValueItems as $item)
                        <div class="rounded-lg border border-slate-200 p-3"><div class="flex justify-between"><strong>{{ $item->name }}</strong><span>{{ number_format($item->stock_value, 2) }}</span></div><p class="text-xs text-gray-500">Qty {{ number_format($item->quantity_on_hand, 2) }} at {{ number_format($item->unit_cost, 2) }}</p></div>
                    @empty
                        <p class="text-sm text-gray-500">No high-value stock data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Supplier Exposure</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($topSuppliers as $supplier)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><div><p class="font-semibold">{{ $supplier->supplier?->name ?? 'Unknown Supplier' }}</p><p class="text-xs text-gray-500">Items {{ number_format($supplier->item_count) }}</p></div><strong>{{ number_format($supplier->stock_value, 2) }}</strong></div>
                    @empty
                        <p class="text-sm text-gray-500">No supplier metrics.</p>
                    @endforelse
                </div>
            </div>
            <div class="panel">
                <h3 class="text-lg font-bold text-gray-900">Value by Category</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($valueByCategory as $category)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3"><span class="font-semibold">{{ $category->category_name }}</span><strong>{{ number_format($category->category_value, 2) }}</strong></div>
                    @empty
                        <p class="text-sm text-gray-500">No category-level value data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
