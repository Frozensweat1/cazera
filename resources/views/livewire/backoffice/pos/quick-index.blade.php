<div>
    <div class="space-y-4">
        <!-- TOP NAVIGATION / CONTROLS BAR -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-[#0e1726] p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-black text-lg">
                    ⚡
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white-light">Quick POS</h1>
                        <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-950/60 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                            Fast Counter Mode
                        </span>
                    </div>
                    <p class="text-xs text-gray-500">
                        @if ($branchId)
                            Active Branch: <span class="font-medium text-slate-700 dark:text-slate-300">{{ session('branch_name') ?? 'Branch #' . $branchId }}</span>
                        @else
                            No branch selected
                        @endif
                    </p>
                </div>
            </div>

            <!-- MODULE TABS & SWITCHER BUTTONS -->
            <div class="flex flex-wrap items-center gap-2">
                @if ($modules->count() > 1)
                    <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 p-1">
                        @foreach ($modules as $module)
                            @php
                                $moduleCount = collect($cart)->where('module_id', $module->id)->sum('qty');
                            @endphp
                            <button type="button"
                                wire:click="setActiveModule({{ $module->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-150 {{ (int) $activeModuleId === (int) $module->id ? 'bg-primary text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                                <span>{{ $module->name }}</span>
                                @if ($moduleCount > 0)
                                    <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[10px] font-bold">
                                        {{ $moduleCount }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                <!-- TODAY'S SALES BUTTON -->
                <button type="button"
                    wire:click="loadDailySales"
                    x-on:click="$dispatch('open-modal', 'quick-today-sales-modal')"
                    class="btn btn-sm btn-outline-primary inline-flex items-center gap-1.5">
                    <x-heroicon-o-clock class="w-4 h-4" />
                    <span>Today's Sales</span>
                </button>

                <!-- SWITCH TO STANDARD POS BUTTON -->
                <a href="{{ route('backoffice.pos') }}"
                    class="btn btn-sm btn-secondary inline-flex items-center gap-1.5"
                    title="Switch to detailed standard interface">
                    <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                    <span>Standard POS</span>
                </a>
            </div>
        </div>

        @if (empty($branchId))
            <div class="panel text-center py-12">
                <div class="mx-auto w-12 h-12 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mb-3">!</div>
                <h3 class="text-base font-semibold text-slate-800 dark:text-white">Please select a branch</h3>
                <p class="text-sm text-gray-500 mt-1">Select an active branch to begin taking orders in Quick POS.</p>
            </div>
        @elseif ($modules->isEmpty())
            <div class="panel text-center py-12">
                <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center mb-3">i</div>
                <h3 class="text-base font-semibold text-slate-800 dark:text-white">No assigned POS modules</h3>
                <p class="text-sm text-gray-500 mt-1">No POS modules are assigned to your account for this branch.</p>
            </div>
        @else
            <!-- MAIN QUICK POS GRID: LEFT (CATALOG) & RIGHT (CART / TICKET) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

                <!-- LEFT SECTION: SEARCH, CATEGORIES, PRODUCT CARDS (7 OR 8 COLS) -->
                <div class="lg:col-span-7 xl:col-span-8 space-y-3.5">

                    <!-- SEARCH AND CATEGORY BAR -->
                    <div class="bg-white dark:bg-[#0e1726] p-3 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </span>
                            <input type="text"
                                wire:model.live.debounce.250ms="quickSearch"
                                placeholder="Search menu items by name, category, code..."
                                class="form-input pl-9 pr-8 text-sm w-full rounded-lg bg-slate-50 dark:bg-[#1b2e4b] border-slate-200 dark:border-slate-700 focus:bg-white dark:focus:bg-[#0e1726]" />
                            @if ($quickSearch)
                                <button type="button"
                                    wire:click="$set('quickSearch', '')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </button>
                            @endif
                        </div>

                        <!-- CATEGORY PILLS -->
                        @if ($categories->isNotEmpty())
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar text-xs">
                                <button type="button"
                                    wire:click="selectCategory(null)"
                                    class="px-3 py-1.5 font-medium rounded-full shrink-0 transition-colors {{ $selectedCategoryId === null ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                                    All Items
                                </button>
                                @foreach ($categories as $category)
                                    <button type="button"
                                        wire:click="selectCategory({{ $category->id }})"
                                        class="px-3 py-1.5 font-medium rounded-full shrink-0 transition-colors {{ (int) $selectedCategoryId === (int) $category->id ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                                        {{ $category->name }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- PRODUCTS GRID -->
                    @php
                        $activeItems = $menuItemsByModule->get($activeModuleId, collect());
                    @endphp

                    @if ($activeItems->isEmpty())
                        <div class="panel py-16 text-center">
                            <div class="mx-auto w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                <x-heroicon-o-face-frown class="w-6 h-6" />
                            </div>
                            <h3 class="font-semibold text-slate-700 dark:text-slate-300">No menu items found</h3>
                            <p class="text-xs text-gray-500 mt-1">Try clearing your search or choosing another category.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                            @foreach ($activeItems as $item)
                                @php
                                    $inCartItem = collect($cart)->first(fn ($line) => (int) $line['menu_item_id'] === (int) $item->id);
                                    $inCartQty = $inCartItem ? (int) $inCartItem['qty'] : 0;
                                    $isOutOfStock = $item->is_trackable && (float) $item->quantity <= 0;
                                    $imageUrl = $this->menuItemImageUrl($item);
                                @endphp

                                <button type="button"
                                    @if ($isOutOfStock) disabled @else wire:click="addToCart({{ $item->id }}, {{ $activeModuleId }})" @endif
                                    class="relative group text-left rounded-xl border bg-white dark:bg-[#0e1726] p-3 transition-all duration-150 flex flex-col justify-between overflow-hidden
                                        {{ $isOutOfStock ? 'opacity-50 cursor-not-allowed border-dashed border-slate-300' : 'hover:border-primary hover:shadow-md active:scale-[0.98] border-slate-200 dark:border-slate-800' }}
                                        {{ $inCartQty > 0 ? 'ring-2 ring-primary border-primary bg-primary/[0.02]' : '' }}">

                                    <!-- IN CART QUANTITY BADGE -->
                                    @if ($inCartQty > 0)
                                        <div class="absolute top-2 right-2 z-10 bg-primary text-white text-xs font-black px-2 py-0.5 rounded-full shadow-sm">
                                            ×{{ $inCartQty }}
                                        </div>
                                    @endif

                                    <!-- PRODUCT IMAGE OR FALLBACK -->
                                    <div class="relative w-full aspect-square rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden mb-2.5 flex items-center justify-center">
                                        @if ($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" />
                                        @else
                                            <span class="text-2xl font-black text-slate-400/80 uppercase">
                                                {{ mb_substr($item->name, 0, 2) }}
                                            </span>
                                        @endif

                                        @if ($isOutOfStock)
                                            <div class="absolute inset-0 bg-black/60 flex items-center justify-center text-white text-xs font-bold uppercase tracking-wider">
                                                Out of stock
                                            </div>
                                        @endif
                                    </div>

                                    <!-- PRODUCT INFO -->
                                    <div class="space-y-1">
                                        <h4 class="font-semibold text-sm text-slate-800 dark:text-white-light line-clamp-2 leading-snug">
                                            {{ $item->name }}
                                        </h4>
                                        <div class="flex items-center justify-between pt-1">
                                            <span class="text-sm font-extrabold text-primary">
                                                <x-ui.money :amount="$item->price" />
                                            </span>
                                            @if ($item->is_trackable)
                                                <span class="text-[10px] text-gray-400">
                                                    {{ number_format((float) $item->quantity, 0) }} left
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- RIGHT SECTION: FAST ORDER TICKET & RAPID CHECKOUT (5 OR 4 COLS) -->
                <div class="lg:col-span-5 xl:col-span-4">
                    <div class="bg-white dark:bg-[#0e1726] rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 flex flex-col justify-between sticky top-4">

                        <!-- TICKET HEADER -->
                        <div class="border-b border-slate-200 dark:border-slate-800 pb-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-base text-slate-900 dark:text-white-light flex items-center gap-1.5">
                                        <span>Current Ticket</span>
                                        @if ($editingSaleId)
                                            <span class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded font-medium">Edit: {{ $editingSaleNumber }}</span>
                                        @endif
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        {{ count($cart) }} {{ str('item')->plural(count($cart)) }} in order
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    @if ($editingSaleId)
                                        <button type="button" wire:click="cancelEdit" class="text-xs text-amber-700 hover:underline">
                                            Cancel
                                        </button>
                                    @elseif (count($cart) > 0)
                                        <button type="button" wire:click="clearCart" class="text-xs text-red-600 hover:text-red-800 font-semibold flex items-center gap-1">
                                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                            <span>Clear</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- ORDER CONTEXT: TYPE, TABLE, CUSTOMER -->
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <!-- ORDER TYPE -->
                                <div class="relative">
                                    <select wire:model.live="sale_type" class="form-select text-xs py-1.5 w-full rounded-lg bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700">
                                        <option value="dine_in">🍽️ Dine In</option>
                                        <option value="takeaway">🥡 Takeaway</option>
                                        <option value="delivery">🛵 Delivery</option>
                                        <option value="online">🌐 Online</option>
                                    </select>
                                </div>

                                <!-- TABLE SELECT (IF DINE IN) -->
                                @if ($sale_type === 'dine_in')
                                    <div class="relative">
                                        <select wire:model="table_id" class="form-select text-xs py-1.5 w-full rounded-lg bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700">
                                            <option value="">Select Table...</option>
                                            @foreach ($availableTables as $tbl)
                                                <option value="{{ $tbl->id }}">{{ $tbl->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="flex items-center text-gray-400 italic px-2">
                                        No table required
                                    </div>
                                @endif
                            </div>

                            <!-- CUSTOMER SELECT / CHIP -->
                            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-900/60 p-2 rounded-lg text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <x-heroicon-o-user class="w-4 h-4 text-slate-400 shrink-0" />
                                    <span class="font-medium text-slate-700 dark:text-slate-300 truncate">
                                        {{ $customer_id ? ($customer_search ?: 'Customer #'.$customer_id) : 'Walk-in Customer' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if ($customer_id)
                                        <button type="button" wire:click="clearCustomer" class="text-red-500 hover:text-red-700 text-xs font-semibold">
                                            Clear
                                        </button>
                                    @endif
                                    <button type="button" x-on:click="$dispatch('open-modal', 'quick-order-options-modal')" class="text-primary hover:underline text-xs font-semibold">
                                        {{ $customer_id ? 'Options' : '+ Add/Search' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- CART ITEMS LIST (SCROLLABLE) -->
                        <div class="py-3 divide-y divide-slate-100 dark:divide-slate-800 max-h-[360px] overflow-y-auto pr-1">
                            @forelse ($cartLines as $line)
                                <div class="py-2.5 flex items-center justify-between gap-2" wire:key="quick-cart-{{ $line['module_id'] }}-{{ $line['menu_item_id'] }}">
                                    <div class="min-w-0 flex-1">
                                        <h5 class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                                            {{ $line['item_name'] }}
                                        </h5>
                                        <p class="text-xs text-gray-500">
                                            <x-ui.money :amount="$line['unit_price']" />
                                            @if ($mixedCartModuleCount > 1 && !empty($line['module_name']))
                                                <span class="text-[10px] text-slate-400">({{ $line['module_name'] }})</span>
                                            @endif
                                        </p>
                                    </div>

                                    <!-- QUANTITY STEPPER -->
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button"
                                            wire:click="decrementCartItem({{ $line['menu_item_id'] }}, {{ $line['module_id'] }})"
                                            class="w-7 h-7 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center font-bold text-slate-700 dark:text-slate-300">
                                            -
                                        </button>
                                        <span class="w-7 text-center font-bold text-sm text-slate-900 dark:text-white">
                                            {{ $line['qty'] }}
                                        </span>
                                        <button type="button"
                                            wire:click="incrementCartItem({{ $line['menu_item_id'] }}, {{ $line['module_id'] }})"
                                            class="w-7 h-7 rounded-md bg-primary/10 hover:bg-primary/20 text-primary flex items-center justify-center font-bold">
                                            +
                                        </button>
                                    </div>

                                    <!-- LINE SUBTOTAL -->
                                    <div class="w-16 text-right shrink-0">
                                        <span class="font-bold text-sm text-slate-900 dark:text-white">
                                            <x-ui.money :amount="$line['subtotal']" />
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-gray-400">
                                    <div class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-2 text-slate-400">
                                        <x-heroicon-o-shopping-bag class="w-5 h-5" />
                                    </div>
                                    <p class="text-xs">Ticket is empty</p>
                                    <p class="text-[11px] text-gray-400">Tap items on the left to add them</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- TOTALS & QUICK PAYMENT SECTION -->
                        @php
                            $subtotal = $orderSummary['subtotal'];
                            $tax = $orderSummary['tax'];
                            $serviceCharge = $orderSummary['service_charge'];
                            $discountAmount = $orderSummary['discount'];
                            $total = $orderSummary['total'];
                        @endphp

                        <div class="border-t border-slate-200 dark:border-slate-800 pt-3 space-y-3">
                            <!-- TOTALS BREAKDOWN -->
                            <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                <div class="flex justify-between">
                                    <span>Subtotal</span>
                                    <span class="font-medium text-slate-800 dark:text-white"><x-ui.money :amount="$subtotal" /></span>
                                </div>
                                @if ((float) $tax > 0)
                                    <div class="flex justify-between">
                                        <span>Tax</span>
                                        <span><x-ui.money :amount="$tax" /></span>
                                    </div>
                                @endif
                                @if ((float) $discountAmount > 0)
                                    <div class="flex justify-between text-emerald-600">
                                        <span>Discount</span>
                                        <span>-<x-ui.money :amount="$discountAmount" /></span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-baseline pt-1.5 border-t border-dashed border-slate-200 dark:border-slate-700">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">Total</span>
                                    <span class="text-2xl font-black text-primary"><x-ui.money :amount="$total" /></span>
                                </div>
                            </div>

                            @if ($errors->has('cart'))
                                <p class="text-xs text-danger font-semibold">{{ $errors->first('cart') }}</p>
                            @endif

                            <!-- QUICK TENDER PRESETS -->
                            @if ($total > 0)
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold mb-1">Quick Tender (Cash)</p>
                                    <div class="grid grid-cols-4 gap-1.5 text-xs">
                                        <button type="button"
                                            wire:click="payWithTender('cash', {{ $total }})"
                                            class="py-1 px-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-bold hover:bg-slate-100 text-slate-800 dark:text-white text-center">
                                            Exact
                                        </button>
                                        @php
                                            $presets = [10, 20, 50, 100, 200];
                                            $shownPresets = collect($presets)->filter(fn ($p) => $p >= $total)->take(3);
                                            if ($shownPresets->isEmpty()) {
                                                $ceil10 = ceil($total / 10) * 10;
                                                $ceil50 = ceil($total / 50) * 50;
                                                $ceil100 = ceil($total / 100) * 100;
                                                $shownPresets = collect([$ceil10, $ceil50, $ceil100])->unique();
                                            }
                                        @endphp
                                        @foreach ($shownPresets as $preset)
                                            <button type="button"
                                                wire:click="payWithTender('cash', {{ $preset }})"
                                                class="py-1 px-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-semibold hover:bg-slate-100 text-slate-800 dark:text-white text-center">
                                                GHS {{ $preset }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- ACTION BUTTONS: FAST CASH, CARD, MOBILE MONEY, MORE -->
                            <div class="space-y-2">
                                <!-- PRIMARY QUICK PAY (CASH) -->
                                <button type="button"
                                    @if ($total <= 0 || empty($cart)) disabled @else wire:click="quickPay('cash')" @endif
                                    class="w-full py-3 px-4 rounded-xl font-black text-white text-base shadow-lg transition-all duration-150 flex items-center justify-center gap-2
                                        {{ $total <= 0 || empty($cart) ? 'bg-slate-300 dark:bg-slate-800 cursor-not-allowed shadow-none' : 'bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] shadow-emerald-600/30' }}">
                                    <x-heroicon-o-banknotes class="w-5 h-5" />
                                    <span>Pay Cash (<x-ui.money :amount="$total" />)</span>
                                </button>

                                <!-- SECONDARY INSTANT PAY: CARD & MOBILE MONEY -->
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button"
                                        @if ($total <= 0 || empty($cart)) disabled @else wire:click="quickPay('card')" @endif
                                        class="py-2.5 px-3 rounded-lg font-bold text-xs border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center justify-center gap-1.5 transition-colors
                                            {{ $total <= 0 || empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        <x-heroicon-o-credit-card class="w-4 h-4 text-blue-500" />
                                        <span>Card</span>
                                    </button>

                                    <button type="button"
                                        @if ($total <= 0 || empty($cart)) disabled @else wire:click="quickPay('mobile_money')" @endif
                                        class="py-2.5 px-3 rounded-lg font-bold text-xs border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center justify-center gap-1.5 transition-colors
                                            {{ $total <= 0 || empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        <x-heroicon-o-device-phone-mobile class="w-4 h-4 text-amber-500" />
                                        <span>MoMo</span>
                                    </button>
                                </div>

                                <!-- ADVANCED / SPLIT / DEBT OPTIONS MODAL TRIGGER -->
                                <button type="button"
                                    x-on:click="$dispatch('open-modal', 'quick-order-options-modal')"
                                    class="w-full py-2 px-3 text-xs font-semibold rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 flex items-center justify-center gap-1.5">
                                    <x-heroicon-o-adjustments-horizontal class="w-4 h-4" />
                                    <span>Discount, Split Payment, Kitchen Notes</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL 1: ADVANCED ORDER OPTIONS (CUSTOMER, SPLIT PAY, NOTES, DISCOUNT) -->
        <x-ui.modal name="quick-order-options-modal" maxWidth="2xl">
            <x-slot:title>Order &amp; Payment Options</x-slot:title>

            <div class="space-y-5">
                <!-- CUSTOMER SELECTION & QUICK REGISTRATION -->
                <div class="space-y-3">
                    <h4 class="font-bold text-sm text-slate-800 dark:text-white flex items-center gap-1.5">
                        <x-heroicon-o-user class="w-4 h-4 text-primary" />
                        <span>Customer</span>
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="relative">
                            <x-ui.input label="Search Customer" name="customer_search"
                                wire:model.live.debounce.300ms="customer_search"
                                placeholder="Search by name, phone, email..." autocomplete="off" />
                            @if ($customer_id)
                                <button type="button"
                                    class="absolute top-8 right-2 rounded px-2 py-0.5 text-xs font-semibold text-danger hover:bg-danger-light"
                                    wire:click="clearCustomer">Clear</button>
                            @elseif (trim($customer_search) !== '')
                                <div class="absolute z-[9999] mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-[#0e1726]">
                                    @forelse ($customers as $c)
                                        <button type="button"
                                            class="block w-full px-3 py-2 text-left text-xs hover:bg-slate-50 dark:hover:bg-white/5 border-b border-slate-100 dark:border-slate-800"
                                            wire:click="selectCustomer({{ $c->id }})">
                                            <span class="block font-semibold text-slate-900 dark:text-white-light">{{ $c->name }}</span>
                                            <span class="block text-[10px] text-slate-500">{{ $c->phone }}</span>
                                        </button>
                                    @empty
                                        <div class="p-3 text-center text-xs text-gray-500">No matching customer</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>

                        <!-- DINING TABLE SELECTION -->
                        <div>
                            <x-ui.select label="Dining Table (for Dine In)" name="table_id" wire:model="table_id">
                                <option value="">No table assigned</option>
                                @foreach ($availableTables as $tbl)
                                    <option value="{{ $tbl->id }}">{{ $tbl->name }} ({{ $tbl->status }})</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>
                </div>

                <!-- DISCOUNT & KITCHEN NOTIFICATION -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <div>
                        <x-ui.select label="Apply Discount" name="discount_id" wire:model.live="discount_id">
                            <option value="">No discount</option>
                            @foreach ($availableDiscounts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->type === 'percentage' ? $d->value.'%' : 'GHS '.$d->value }})</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" wire:model="notifyKitchen" class="form-checkbox text-primary rounded" />
                            <span>Send ticket to Kitchen Display immediately</span>
                        </label>
                    </div>
                </div>

                <!-- NOTES -->
                <div>
                    <x-ui.textarea label="Kitchen / Order Notes" name="notes" wire:model.defer="notes" placeholder="Special cooking instructions, allergies, table location..." rows="2" />
                </div>

                <!-- SPLIT / CUSTOM PAYMENT ROWS -->
                <div class="pt-3 border-t border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-white flex items-center gap-1.5">
                            <x-heroicon-o-credit-card class="w-4 h-4 text-primary" />
                            <span>Payment Split &amp; Tender</span>
                        </h4>
                        <button type="button" wire:click="addPaymentRow" class="text-xs text-primary hover:underline font-semibold flex items-center gap-1">
                            <span>+ Add Payment Method</span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($splitPayments as $index => $row)
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center bg-slate-50 dark:bg-slate-900/60 p-2 rounded-lg text-xs" wire:key="quick-split-{{ $index }}">
                                <div class="sm:col-span-5">
                                    <select wire:model.live="splitPayments.{{ $index }}.method" class="form-select text-xs py-1.5 w-full rounded-md">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="wallet">Wallet</option>
                                        <option value="credit_sale">Credit Sale / Debt</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-4">
                                    <input type="number" step="0.01" min="0" wire:model.lazy="splitPayments.{{ $index }}.amount" placeholder="Amount (GHS)" class="form-input text-xs py-1.5 w-full rounded-md" />
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="text" wire:model.defer="splitPayments.{{ $index }}.transaction_reference" placeholder="Ref #" class="form-input text-xs py-1.5 w-full rounded-md" />
                                </div>
                                <div class="sm:col-span-1 text-center">
                                    @if (count($splitPayments) > 1)
                                        <button type="button" wire:click="removePaymentRow({{ $index }})" class="text-red-500 hover:text-red-700">
                                            <x-heroicon-o-trash class="w-4 h-4 mx-auto" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <div class="flex justify-between items-center w-full">
                    <button type="button" x-on:click="$dispatch('close-modal', 'quick-order-options-modal')" class="btn btn-sm btn-outline-secondary">
                        Done
                    </button>
                    <button type="button"
                        wire:click="saveSale"
                        x-on:click="$dispatch('close-modal', 'quick-order-options-modal')"
                        class="btn btn-sm btn-primary">
                        Confirm &amp; Place Order
                    </button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        <!-- MODAL 2: TODAY'S SALES & RECENT PAYMENTS -->
        <x-ui.modal name="quick-today-sales-modal" maxWidth="4xl">
            <x-slot:title>Today's Sales</x-slot:title>

            <div class="space-y-4">
                <div class="overflow-x-auto max-h-96">
                    <x-ui.table>
                        <thead>
                            <tr>
                                <th>Sale #</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lastSales as $sale)
                                <tr wire:key="quick-sale-{{ $sale->id }}">
                                    <td class="font-semibold">{{ $sale->sale_number }}</td>
                                    <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                    <td><x-ui.money :amount="$sale->total" /></td>
                                    <td>
                                        @if ((float) $sale->remaining_balance > 0)
                                            <span class="font-semibold text-danger"><x-ui.money :amount="$sale->remaining_balance" /></span>
                                        @else
                                            <span class="text-success font-semibold">Paid</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-outline-primary text-xs">{{ ucfirst($sale->status) }}</span></td>
                                    <td class="text-xs text-gray-500">{{ $sale->sale_date?->format('h:i A') }}</td>
                                    <td class="text-center space-x-1">
                                        @if ((float) $sale->remaining_balance > 0)
                                            <x-ui.button type="button" size="sm" variant="success" wire:click="openRecentPayment({{ $sale->id }})">Pay</x-ui.button>
                                        @endif
                                        <x-ui.button type="button" size="sm" variant="secondary" wire:click="viewReceipt({{ $sale->id }})" icon="eye">Receipt</x-ui.button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-gray-500">No sales recorded yet today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>
            </div>

            <x-slot:footer>
                <div class="flex justify-end">
                    <button type="button" x-on:click="$dispatch('close-modal', 'quick-today-sales-modal')" class="btn btn-sm btn-secondary">
                        Close
                    </button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        <!-- MODAL 3: RECEIPT MODAL (80mm Thermal Friendly) -->
        <div x-data="{
            printReceipt() {
                const receipt = document.getElementById('quick-receipt-print-area');
                if (!receipt) return;

                const printWindow = window.open('', 'posReceiptPrint', 'width=420,height=720');
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
                                .receipt-shell img { display: block; max-width: 64px; max-height: 64px; margin: 0 auto 10px; object-fit: contain; }
                            </style>
                        </head>
                        <body>
                            <div class='receipt-shell'>${receipt.innerHTML}</div>
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => printWindow.print(), 250);
            }
        }">
            <x-ui.modal name="pos-receipt-modal" maxWidth="2xl">
                <x-slot:title>Sale Receipt</x-slot:title>

                @if ($receiptSale)
                    <div id="quick-receipt-print-area" class="space-y-5">
                        <div class="border-b border-dashed border-slate-300 pb-4 text-center">
                            @if ($receiptSettings['logo'])
                                <img src="{{ $receiptSettings['logo'] }}" alt="{{ $receiptSettings['business_name'] }} logo"
                                    class="mx-auto mb-3 h-16 max-w-[5rem] object-contain">
                            @endif
                            <h2 class="text-xl font-extrabold">{{ $receiptSettings['business_name'] }}</h2>
                            @if ($receiptSettings['tagline'])
                                <p class="text-sm text-gray-500">{{ $receiptSettings['tagline'] }}</p>
                            @endif
                            @if ($receiptSettings['address'])
                                <p class="text-xs text-gray-500">{{ $receiptSettings['address'] }}</p>
                            @endif
                            <p class="text-xs text-gray-500">
                                {{ collect([$receiptSettings['phone'], $receiptSettings['email']])->filter()->implode(' - ') }}
                            </p>
                            @if ($receiptSettings['whatsapp'])
                                <p class="text-xs text-gray-500">WhatsApp: {{ $receiptSettings['whatsapp'] }}</p>
                            @endif
                            <p class="mt-2 text-sm text-gray-500">{{ $receiptSale->module_names }} POS Receipt</p>
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

                        <div class="overflow-x-auto">
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
                                            <td class="py-2 text-right"><x-ui.money :amount="$item->unit_price" /></td>
                                            <td class="py-2 text-right"><x-ui.money :amount="$item->total" /></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="space-y-2 border-b border-dashed border-slate-300 pb-4 text-sm">
                            <div class="flex justify-between"><span>Subtotal</span><span><x-ui.money :amount="$receiptSale->subtotal" /></span></div>
                            @forelse ($receiptTaxes as $taxLine)
                                <div class="flex justify-between">
                                    <span>
                                        {{ $taxLine['name'] }}
                                        @if ($taxLine['rate'] !== null)
                                            ({{ number_format($taxLine['rate'], 2) }}%)
                                        @endif
                                    </span>
                                    <span><x-ui.money :amount="$taxLine['amount']" /></span>
                                </div>
                            @empty
                                <div class="flex justify-between"><span>Tax</span><span><x-ui.money :amount="$receiptSale->tax" /></span></div>
                            @endforelse
                            <div class="flex justify-between font-semibold"><span>Total Tax</span><span><x-ui.money :amount="$receiptSale->tax" /></span></div>
                            <div class="flex justify-between"><span>Service Charge</span><span><x-ui.money :amount="$receiptSale->service_charge" /></span></div>
                            <div class="flex justify-between"><span>Discount</span><span><x-ui.money :amount="$receiptSale->discount" /></span></div>
                            <div class="flex justify-between text-base font-extrabold"><span>Total</span><span><x-ui.money :amount="$receiptSale->total" /></span></div>
                            @if ((float) $receiptSale->refunded_amount > 0)
                                <div class="flex justify-between text-red-700"><span>Refunded</span><span>-<x-ui.money :amount="$receiptSale->refunded_amount" /></span></div>
                            @endif
                            <div class="flex justify-between"><span>Paid</span><span><x-ui.money :amount="$receiptSale->paid_amount" /></span></div>
                            <div class="flex justify-between"><span>Balance</span><span><x-ui.money :amount="$receiptSale->remaining_balance" /></span></div>
                        </div>

                        <div class="space-y-2 text-sm">
                            <p class="font-semibold">Payments</p>
                            @forelse ($receiptSale->payments as $payment)
                                <div class="flex justify-between gap-4">
                                    <span>{{ $payment->status === 'refunded' ? 'Refund' : ucfirst(str_replace('_', ' ', $payment->method)) }}
                                        @if ($payment->transaction_reference)
                                            <span class="text-gray-400">({{ $payment->transaction_reference }})</span>
                                        @endif
                                    </span>
                                    <span @class(['text-red-700' => $payment->status === 'refunded'])>
                                        {{ $payment->status === 'refunded' ? '-' : '' }}<x-ui.money :amount="$payment->amount" />
                                    </span>
                                </div>
                            @empty
                                <p class="text-gray-500">No payment recorded.</p>
                            @endforelse
                        </div>

                        <p class="pt-2 text-center text-xs text-gray-500">Thank you for your visit.</p>
                    </div>
                @else
                    <p class="text-gray-500">Receipt is not available.</p>
                @endif

                <x-slot:footer>
                    <div class="flex flex-wrap justify-end gap-3">
                        <x-ui.button type="button" variant="secondary" icon="printer"
                            x-on:click="printReceipt()">Print</x-ui.button>
                        <x-ui.button type="button" icon="arrow-down-tray"
                            x-on:click="printReceipt()">Print / Save PDF</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>

            <!-- MODAL 4: RECORD RECENT PAYMENT (FOR UNPAID BILLS) -->
            <x-ui.modal name="recent-sale-payment-modal" maxWidth="xl">
                <x-slot:title>Record Sale Payment</x-slot:title>

                @if ($recentPaymentSale)
                    <div class="space-y-5">
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-semibold text-emerald-950">{{ $recentPaymentSale->sale_number }}</p>
                                    <p class="text-sm text-emerald-700">{{ $recentPaymentSale->customer?->name ?? 'Walk-in Customer' }}</p>
                                    <p class="text-xs text-emerald-700">{{ $recentPaymentSale->branch?->name }} / {{ $recentPaymentSale->module_names }}</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-xs uppercase tracking-wide text-emerald-700">Outstanding</p>
                                    <p class="text-xl font-extrabold text-emerald-950"><x-ui.money :amount="$recentPaymentSale->remaining_balance" /></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <x-ui.select label="Method" name="recent_payment_method" wire:model="recent_payment_method">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile_money">Mobile money</option>
                                <option value="bank_transfer">Bank transfer</option>
                                <option value="wallet">Wallet</option>
                            </x-ui.select>
                            <x-ui.input label="Amount (GHS)" type="number" name="recent_payment_amount" wire:model.change="recent_payment_amount" min="0.01" step="0.01" />
                            <x-ui.input label="Reference" name="recent_payment_reference" wire:model.blur="recent_payment_reference" placeholder="Optional" />
                        </div>
                    </div>
                @endif

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'recent-sale-payment-modal')">
                            Cancel
                        </x-ui.button>
                        <x-ui.button type="button" wire:click="recordRecentPayment" wire:target="recordRecentPayment" wire:loading.attr="disabled" icon="check">
                            <span wire:loading.remove wire:target="recordRecentPayment">Save Payment</span>
                            <span wire:loading wire:target="recordRecentPayment">Saving...</span>
                        </x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        </div>
    </div>
</div>
