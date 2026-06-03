<div>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Kitchen Display System</h1>
                <p class="text-gray-500">Kitchen tickets are grouped by order so every item from the same sale stays together.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.checkbox label="Show completed items" name="showCompleted" wire:model.live="showCompleted" />
            </div>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
                    <option value="">All Kitchen Statuses</option>
                    <option value="queued">Queued</option>
                    <option value="cooking">Cooking</option>
                    <option value="ready">Ready</option>
                    <option value="completed">Completed</option>
                </x-ui.select>
            </div>
        </div>

        @php
            $statusClasses = [
                'pending' => 'border-slate-200 bg-slate-50 text-slate-700',
                'queued' => 'border-amber-200 bg-amber-50 text-amber-700',
                'cooking' => 'border-blue-200 bg-blue-50 text-blue-700',
                'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'completed' => 'border-slate-200 bg-slate-100 text-slate-600',
                'mixed' => 'border-violet-200 bg-violet-50 text-violet-700',
            ];
        @endphp

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @forelse ($kitchenOrders as $order)
                @php
                    $orderStatus = $this->orderKitchenStatus($order);
                    $orderStatusClass = $statusClasses[$orderStatus] ?? $statusClasses['queued'];
                    $orderWaitTime = $this->formatOrderWaitTime($order);
                    $pendingCount = $order->items->whereNotIn('kitchen_status', ['completed'])->count();
                @endphp

                <article class="panel flex h-full flex-col overflow-hidden border border-slate-200 p-0">
                    <header class="border-b border-slate-100 bg-slate-50/70 p-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wide text-gray-500">Order</span>
                                    <span class="font-mono text-base font-extrabold text-slate-950">{{ $order->sale_number }}</span>
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $orderStatusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $orderStatus)) }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ $order->customer?->name ?? 'Walk-in' }}
                                    &middot; {{ $order->module?->name ?? 'No module' }}
                                    &middot; {{ $order->sale_date?->format('h:i A') ?? $order->created_at?->format('h:i A') }}
                                </p>
                            </div>

                            <div class="grid grid-cols-3 gap-2 text-center sm:min-w-[17rem]">
                                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                    <p class="text-[11px] font-bold uppercase text-gray-500">Items</p>
                                    <p class="text-lg font-extrabold text-slate-950">{{ number_format($order->items->count()) }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                    <p class="text-[11px] font-bold uppercase text-gray-500">Open</p>
                                    <p class="text-lg font-extrabold text-slate-950">{{ number_format($pendingCount) }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2">
                                    <p class="text-[11px] font-bold uppercase text-gray-500">Wait</p>
                                    <p class="text-lg font-extrabold text-slate-950">{{ $orderWaitTime }}</p>
                                </div>
                            </div>
                        </div>

                        @if ($order->notes)
                            <div class="mt-3 rounded-lg border border-amber-100 bg-amber-50 p-3 text-sm text-amber-800">
                                {{ $order->notes }}
                            </div>
                        @endif
                    </header>

                    <div class="flex flex-1 flex-col divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            @php
                                $itemStatusClass = $statusClasses[$item->kitchen_status] ?? $statusClasses['pending'];
                                $waitTime = $this->formatWaitTime($item);
                                $imageUrl = $this->menuItemImageUrl($item);
                            @endphp

                            <section class="p-4">
                                <div class="flex flex-col gap-4 sm:flex-row">
                                    <div class="h-20 w-20 flex-none overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->item_name }}" class="h-full w-full object-cover" loading="lazy">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-50 to-slate-100 text-slate-500">
                                                <svg viewBox="0 0 96 96" class="h-14 w-14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <circle cx="35" cy="48" r="22" fill="#f8fafc" stroke="currentColor" stroke-width="4" />
                                                    <circle cx="35" cy="48" r="10" fill="#f59e0b" opacity=".35" />
                                                    <path d="M67 18h10l4 48a9 9 0 0 1-18 0l4-48Z" fill="#e0f2fe" stroke="currentColor" stroke-width="4" />
                                                    <path d="M66 34h13" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                            <div class="min-w-0">
                                                <h2 class="truncate text-lg font-extrabold text-slate-950">{{ $item->item_name }}</h2>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    Qty {{ number_format($item->qty, 0) }}
                                                    &middot; Wait {{ $waitTime }}
                                                    &middot; Added {{ $item->created_at?->format('h:i A') }}
                                                </p>
                                            </div>
                                            <span class="inline-flex w-fit shrink-0 items-center rounded-full border px-3 py-1 text-xs font-bold {{ $itemStatusClass }}">
                                                {{ ucfirst($item->kitchen_status) }}
                                            </span>
                                        </div>

                                        @if ($item->notes)
                                            <div class="mt-3 rounded-lg border border-amber-100 bg-amber-50 p-3 text-sm text-amber-800">
                                                {{ $item->notes }}
                                            </div>
                                        @endif

                                        <div class="mt-4 flex flex-wrap items-center gap-2">
                                            @if ($item->kitchen_status === 'pending' || $item->kitchen_status === 'queued')
                                                <x-ui.button size="sm" wire:click="markAs({{ $item->id }}, 'cooking')"
                                                    target="markAs({{ $item->id }}, 'cooking')" icon="play">Start</x-ui.button>
                                            @endif

                                            @if ($item->kitchen_status === 'cooking')
                                                <x-ui.button size="sm" wire:click="markAs({{ $item->id }}, 'ready')"
                                                    target="markAs({{ $item->id }}, 'ready')" icon="check">Ready</x-ui.button>
                                            @endif

                                            @if ($item->kitchen_status === 'ready')
                                                <x-ui.button size="sm" wire:click="markAs({{ $item->id }}, 'completed')"
                                                    target="markAs({{ $item->id }}, 'completed')" icon="check">Complete</x-ui.button>
                                            @endif

                                            @if ($item->kitchen_status !== 'completed' && $item->kitchen_status !== 'queued')
                                                <x-ui.button size="sm" variant="outline-danger"
                                                    wire:click="markAs({{ $item->id }}, 'queued')"
                                                    target="markAs({{ $item->id }}, 'queued')" icon="clock">Queue</x-ui.button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </section>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="panel py-12 text-center text-gray-500 xl:col-span-2">
                    No kitchen orders found.
                </div>
            @endforelse
        </div>

        <div>{{ $kitchenOrders->links() }}</div>
    </div>
</div>
