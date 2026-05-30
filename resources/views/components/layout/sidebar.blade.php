@php
    $settings = \App\Support\WebsiteContent::settings();
    $brandName = $settings?->business_name ?: config('app.name', 'Cazera');
    $brandLogo = $settings?->logo ? \App\Support\WebsiteContent::assetPath($settings->logo) : null;
    $brandTagline = $settings?->tagline ?: 'Hospitality ERP';
    $brandInitials = collect(explode(' ', $brandName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $brandInitials = $brandInitials ?: mb_strtoupper(mb_substr($brandName, 0, 2));

    $activeDropdown = match (true) {
        request()->routeIs('dashboard', 'backoffice.dashboards.*') => 'dashboard',
        request()->routeIs('backoffice.branches', 'backoffice.modules', 'backoffice.users', 'backoffice.module-staff', 'backoffice.branch-staff', 'backoffice.roles', 'backoffice.permissions') => 'administration',
        request()->routeIs('backoffice.customers', 'backoffice.customers.*') => 'customers',
        request()->routeIs('backoffice.categories', 'backoffice.menu-items', 'backoffice.menu-item-adjustments') => 'menu_products',
        request()->routeIs('backoffice.inventory-*', 'backoffice.stock-*', 'backoffice.suppliers') => 'inventory',
        request()->routeIs('backoffice.pos', 'backoffice.pos.kitchen') => 'pos',
        request()->routeIs('backoffice.pos.*') => 'sales',
        request()->routeIs('backoffice.daily-production-costs', 'backoffice.expense-categories', 'backoffice.expenses', 'backoffice.net-revenue') => 'production',
        request()->routeIs('backoffice.maintenance.*') => 'maintenance',
        request()->routeIs('backoffice.logs.*') => 'logs',
        request()->routeIs('backoffice.reports*') => 'reports',
        request()->routeIs('website.*', 'backoffice.website.*') => 'website',
        default => null,
    };
    $canSeeAdministration = auth()->user()?->isSuperAdmin() || auth()->user()?->isBranchManager();
    $canManageMenuProducts = auth()->user()?->isSuperAdmin() || auth()->user()?->isBranchManager();
    $canManageInventory = auth()->user()?->isSuperAdmin() || auth()->user()?->isBranchManager() || auth()->user()?->isInventoryManager();
    $canAccessPos = auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'POS Operator']);
    $canAccessKitchenDisplay = auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'Kitchen Staff']);
    $canAccessSalesTransactions = auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'Accountant']);
    $canAccessDashboardHome = auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'POS Operator', 'Accountant']);
    $canAccessDecisionDashboards = auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'Accountant']);
    $canSeePosSection = $canAccessPos || $canAccessKitchenDisplay || $canAccessSalesTransactions;
    $canAccessProductionCosting = auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'Accountant']);
    $canAccessMaintenance = auth()->check();
@endphp

<div :class="{ 'dark text-white-dark': $store.app.semidark }">
    <nav x-data="sidebar"
        class="sidebar fixed bottom-0 top-0 z-50 h-full min-h-screen w-[260px] shadow-[5px_0_25px_0_rgba(94,92,154,0.1)] transition-all duration-300">
        <div class="flex h-full flex-col bg-white dark:bg-[#0e1726]">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 dark:border-white/10">
                <a href="{{ route('dashboard') }}"
                    class="main-logo flex min-w-0 shrink items-center gap-3 rounded-xl outline-none transition focus:ring-2 focus:ring-primary/30">
                    <span
                        class="inline-flex h-11 w-11 flex-none items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 text-sm font-extrabold text-[#f5dfaa] shadow-sm dark:border-white/10 dark:bg-white/10">
                        @if ($brandLogo)
                            <img class="h-full w-full object-contain p-2" src="{{ $brandLogo }}"
                                alt="{{ $brandName }} logo">
                        @else
                            {{ $brandInitials }}
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span
                            class="block truncate text-xl font-extrabold leading-6 tracking-tight text-slate-950 dark:text-white-light">{{ $brandName }}</span>
                        <span
                            class="block truncate text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-white-dark/60">{{ $brandTagline }}</span>
                    </span>
                </a>
                <a href="javascript:;"
                    class="collapse-icon flex h-8 w-8 items-center rounded-full transition duration-300 hover:bg-gray-500/10 rtl:rotate-180 dark:text-white-light dark:hover:bg-dark-light/10"
                    @click="$store.app.toggleSidebar()">
                    <svg class="m-auto h-5 w-5" width="20" height="20" viewbox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                        <path opacity="0.5" d="M16.9998 19L10.9998 12L16.9998 5" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </a>
            </div>

            <ul class="perfect-scrollbar relative min-h-0 flex-1 space-y-0.5 overflow-y-auto overflow-x-hidden p-4 py-0 font-semibold"
                x-data="{ activeDropdown: @js($activeDropdown) }">

                {{-- Dashboard / Analytics --}}
                @if ($canAccessDashboardHome || $canAccessDecisionDashboards)
                <li class="menu nav-item">
                    <button type="button" class="nav-link group" :class="{ 'active': activeDropdown === 'dashboard' }"
                        @click="activeDropdown === 'dashboard' ? activeDropdown = null : activeDropdown = 'dashboard'">
                        <div class="flex items-center">
                            <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.5"
                                    d="M2 12.2039C2 9.91549 2 8.77128 2.5192 7.82274C3.0384 6.87421 3.98695 6.28551 5.88403 5.10813L7.88403 3.86687C9.88939 2.62229 10.8921 2 12 2C13.1079 2 14.1106 2.62229 16.116 3.86687L18.116 5.10812C20.0131 6.28551 20.9616 6.87421 21.4808 7.82274C22 8.77128 22 9.91549 22 12.2039V13.725C22 17.6258 22 19.5763 20.8284 20.7881C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.7881C2 19.5763 2 17.6258 2 13.725V12.2039Z"
                                    fill="currentColor"></path>
                                <path
                                    d="M9 17.25C8.58579 17.25 8.25 17.5858 8.25 18C8.25 18.4142 8.58579 18.75 9 18.75H15C15.4142 18.75 15.75 18.4142 15.75 18C15.75 17.5858 15.4142 17.25 15 17.25H9Z"
                                    fill="currentColor"></path>
                            </svg>
                            <span
                                class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Dashboard</span>
                        </div>
                        <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'dashboard' }">
                            <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </button>
                    <ul x-cloak="" x-show="activeDropdown === 'dashboard'" x-collapse=""
                        class="sub-menu text-gray-500">
                        @if ($canAccessDashboardHome)
                            <li><a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')])>Dashboard Home</a></li>
                        @endif
                        @if ($canAccessDecisionDashboards)
                            <li><a href="{{ route('backoffice.dashboards.quantitative') }}" @class(['active' => request()->routeIs('backoffice.dashboards.quantitative')])>Quantitative</a></li>
                            <li><a href="{{ route('backoffice.dashboards.financial') }}" @class(['active' => request()->routeIs('backoffice.dashboards.financial')])>Financial</a></li>
                            <li><a href="{{ route('backoffice.dashboards.analytical') }}" @class(['active' => request()->routeIs('backoffice.dashboards.analytical')])>Analytical</a></li>
                        @endif
                    </ul>
                </li>
                @endif
                <h2
                    class="-mx-4 mb-1 flex items-center bg-white-light/30 py-3 px-7 font-extrabold uppercase dark:bg-dark dark:bg-opacity-[0.08]">
                    <svg class="hidden h-5 w-4 flex-none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Setting Up</span>
                </h2>
                @if ($canSeeAdministration)
                    {{-- Administration --}}
                    <li class="menu nav-item">
                        <button type="button" class="nav-link group"
                            :class="{ 'active': activeDropdown === 'administration' }"
                            @click="activeDropdown === 'administration' ? activeDropdown = null : activeDropdown = 'administration'">
                            <div class="flex items-center">
                                <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                    viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M12 2C7.02944 2 3 6.02944 3 11C3 15.9706 7.02944 20 12 20C16.9706 20 21 15.9706 21 11C21 6.02944 16.9706 2 12 2ZM12 6C12.5523 6 13 6.44772 13 7C13 7.55228 12.5523 8 12 8C11.4477 8 11 7.55228 11 7C11 6.44772 11.4477 6 12 6ZM13 11C13 10.4477 12.5523 10 12 10C11.4477 10 11 10.4477 11 11V14C11 14.5523 11.4477 15 12 15C12.5523 15 13 14.5523 13 14V11Z"
                                        fill="currentColor"></path>
                                </svg>
                                <span
                                    class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Administration</span>
                            </div>
                            <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'administration' }">
                                <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                        </button>
                        <ul x-cloak="" x-show="activeDropdown === 'administration'" x-collapse=""
                            class="sub-menu text-gray-500">
                            @if (auth()->user()?->isSuperAdmin())
                                <li><a href="{{ route('backoffice.branches') }}" @class(['active' => request()->routeIs('backoffice.branches')])>Branch Management</a></li>
                            @endif
                            <li><a href="{{ route('backoffice.modules') }}" @class(['active' => request()->routeIs('backoffice.modules')])>Manage Modules</a></li>
                            <li><a href="{{ route('backoffice.users') }}" @class(['active' => request()->routeIs('backoffice.users')])>Users</a></li>
                            <li><a href="{{ route('backoffice.module-staff') }}" @class(['active' => request()->routeIs('backoffice.module-staff')])>Assign Modules</a></li>
                            @if (auth()->user()?->isSuperAdmin())
                                <li><a href="{{ route('backoffice.branch-staff') }}" @class(['active' => request()->routeIs('backoffice.branch-staff')])>Assign Branches</a></li>
                                <li><a href="{{ route('backoffice.roles') }}" @class(['active' => request()->routeIs('backoffice.roles')])>Roles</a></li>
                                <li><a href="{{ route('backoffice.permissions') }}" @class(['active' => request()->routeIs('backoffice.permissions')])>Permissions</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Customers Management (separate section) --}}
                <li class="menu nav-item">
                    <button type="button" class="nav-link group"
                        :class="{ 'active': activeDropdown === 'customers' }"
                        @click="activeDropdown === 'customers' ? activeDropdown = null : activeDropdown = 'customers'">
                        <div class="flex items-center">
                            <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2Z"
                                    fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 7C10.8954 7 10 7.89543 10 9C10 10.1046 10.8954 11 12 11C13.1046 11 14 10.1046 14 9C14 7.89543 13.1046 7 12 7Z"
                                    fill="currentColor"></path>
                            </svg>
                            <span
                                class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Customers
                                MGT</span>
                        </div>
                        <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'customers' }">
                            <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </button>
                    <ul x-cloak="" x-show="activeDropdown === 'customers'" x-collapse=""
                        class="sub-menu text-gray-500">
                        <li><a href="{{ route('backoffice.customers') }}" @class(['active' => request()->routeIs('backoffice.customers')])>Customers</a></li>
                        <li><a href="{{ route('backoffice.customers.history') }}" @class(['active' => request()->routeIs('backoffice.customers.history')])>Customer History</a></li>
                    </ul>
                </li>

                @if ($canManageMenuProducts)
                    {{-- Menu / Product Management --}}
                    <li class="menu nav-item">
                        <button type="button" class="nav-link group"
                            :class="{ 'active': activeDropdown === 'menu_products' }"
                            @click="activeDropdown === 'menu_products' ? activeDropdown = null : activeDropdown = 'menu_products'">
                            <div class="flex items-center">
                                <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                    viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4 6C4 4.89543 4.89543 4 6 4H18C19.1046 4 20 4.89543 20 6V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V6Z"
                                        fill="currentColor"></path>
                                    <path d="M8 8H16V10H8V8Z" fill="currentColor"></path>
                                    <path d="M8 12H14V14H8V12Z" fill="currentColor"></path>
                                </svg>
                                <span
                                    class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Menu
                                    / Product MGT</span>
                            </div>
                            <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'menu_products' }">
                                <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                        </button>
                        <ul x-cloak="" x-show="activeDropdown === 'menu_products'" x-collapse=""
                            class="sub-menu text-gray-500">
                            <li><a href="{{ route('backoffice.categories') }}" @class(['active' => request()->routeIs('backoffice.categories')])>Categories</a></li>
                            <li><a href="{{ route('backoffice.menu-items') }}" @class(['active' => request()->routeIs('backoffice.menu-items')])>Menu Items / Products</a></li>
                            <li><a href="{{ route('backoffice.menu-item-adjustments') }}" @class(['active' => request()->routeIs('backoffice.menu-item-adjustments')])>Menu Item Adjustments</a></li>
                        </ul>
                    </li>
                @endif

                @if ($canManageInventory)
                    <h2
                        class="-mx-4 mb-1 flex items-center bg-white-light/30 py-3 px-7 font-extrabold uppercase dark:bg-dark dark:bg-opacity-[0.08]">
                        <svg class="hidden h-5 w-4 flex-none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Inventory</span>
                    </h2>
                    {{-- Inventory Management --}}
                    <li class="menu nav-item">
                        <button type="button" class="nav-link group"
                            :class="{ 'active': activeDropdown === 'inventory' }"
                            @click="activeDropdown === 'inventory' ? activeDropdown = null : activeDropdown = 'inventory'">
                            <div class="flex items-center">
                                <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                    viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M12 2L2 7L12 12L22 7L12 2Z" fill="currentColor"></path>
                                    <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M2 12L12 17L22 12V17L12 22L2 17V12Z" fill="currentColor"></path>
                                </svg>
                                <span
                                    class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Inventory
                                    MGT</span>
                            </div>
                            <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'inventory' }">
                                <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                        </button>
                        <ul x-cloak="" x-show="activeDropdown === 'inventory'" x-collapse=""
                            class="sub-menu text-gray-500">
                            <li><a href="{{ route('backoffice.inventory-dashboard') }}" @class(['active' => request()->routeIs('backoffice.inventory-dashboard')])>Inventory Dashboard</a></li>
                            <li><a href="{{ route('backoffice.inventory-categories') }}" @class(['active' => request()->routeIs('backoffice.inventory-categories')])>Inventory Categories</a></li>
                            <li><a href="{{ route('backoffice.inventory-locations') }}" @class(['active' => request()->routeIs('backoffice.inventory-locations')])>Inventory Locations</a></li>
                            <li><a href="{{ route('backoffice.suppliers') }}" @class(['active' => request()->routeIs('backoffice.suppliers')])>Suppliers</a></li>
                            <li><a href="{{ route('backoffice.inventory-items') }}" @class(['active' => request()->routeIs('backoffice.inventory-items')])>Inventory Items</a></li>
                            <li><a href="{{ route('backoffice.inventory-item-stocks') }}" @class(['active' => request()->routeIs('backoffice.inventory-item-stocks')])>Inventory Stock</a></li>
                            <li><a href="{{ route('backoffice.stock-adjustments') }}" @class(['active' => request()->routeIs('backoffice.stock-adjustments')])>Stock Adjustments</a></li>
                            <li><a href="{{ route('backoffice.stock-transfers') }}" @class(['active' => request()->routeIs('backoffice.stock-transfers')])>Stock Transfers</a></li>
                            <li><a href="{{ route('backoffice.stock-movements') }}" @class(['active' => request()->routeIs('backoffice.stock-movements')])>Stock Movements</a></li>
                        </ul>
                    </li>
                @endif

                @if ($canSeePosSection)
                    <h2
                        class="-mx-4 mb-1 flex items-center bg-white-light/30 py-3 px-7 font-extrabold uppercase dark:bg-dark dark:bg-opacity-[0.08]">
                        <svg class="hidden h-5 w-4 flex-none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>POS & Sales</span>
                    </h2>
                    @if ($canAccessPos || $canAccessKitchenDisplay)
                        {{-- POS System --}}
                        <li class="menu nav-item">
                            <button type="button" class="nav-link group"
                                :class="{ 'active': activeDropdown === 'pos' }"
                                @click="activeDropdown === 'pos' ? activeDropdown = null : activeDropdown = 'pos'">
                                <div class="flex items-center">
                                    <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                        viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                            d="M4 4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V10L14 4H4Z"
                                            fill="currentColor"></path>
                                        <path d="M7 12H13V14H7V12Z" fill="currentColor"></path>
                                    </svg>
                                    <span
                                        class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">POS
                                        System</span>
                                </div>
                                <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'pos' }">
                                    <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </div>
                            </button>
                            <ul x-cloak="" x-show="activeDropdown === 'pos'" x-collapse=""
                                class="sub-menu text-gray-500">
                                @if ($canAccessPos)
                                    <li><a href="{{ route('backoffice.pos') }}" @class(['active' => request()->routeIs('backoffice.pos')])>POS</a></li>
                                @endif
                                @if ($canAccessKitchenDisplay)
                                    <li><a href="{{ route('backoffice.pos.kitchen') }}" @class(['active' => request()->routeIs('backoffice.pos.kitchen')])>Kitchen Display</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if ($canAccessSalesTransactions)
                        {{-- Sales & Transactions --}}
                        <li class="menu nav-item">
                            <button type="button" class="nav-link group"
                                :class="{ 'active': activeDropdown === 'sales' }"
                                @click="activeDropdown === 'sales' ? activeDropdown = null : activeDropdown = 'sales'">
                                <div class="flex items-center">
                                    <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                        viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                            d="M3 6C3 4.89543 3.89543 4 5 4H19C20.1046 4 21 4.89543 21 6V8C21 9.10457 20.1046 10 19 10H5C3.89543 10 3 9.10457 3 8V6Z"
                                            fill="currentColor"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M3 12H21V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V12Z"
                                            fill="currentColor"></path>
                                    </svg>
                                    <span
                                        class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Sales
                                        &amp; Transactions</span>
                                </div>
                                <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'sales' }">
                                    <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </div>
                            </button>
                            <ul x-cloak="" x-show="activeDropdown === 'sales'" x-collapse=""
                                class="sub-menu text-gray-500">
                                <li><a href="{{ route('backoffice.pos.sales') }}" @class(['active' => request()->routeIs('backoffice.pos.sales')])>Sales List</a></li>
                                <li><a href="{{ route('backoffice.pos.transactions') }}" @class(['active' => request()->routeIs('backoffice.pos.transactions')])>Transactions</a></li>
                                <li><a href="{{ route('backoffice.pos.refunds') }}" @class(['active' => request()->routeIs('backoffice.pos.refunds')])>Refunds &amp; Returns</a></li>
                                <li><a href="{{ route('backoffice.pos.debtors') }}" @class(['active' => request()->routeIs('backoffice.pos.debtors')])>Debtors</a></li>
                                <li><a href="{{ route('backoffice.pos.split-payments') }}" @class(['active' => request()->routeIs('backoffice.pos.split-payments')])>Split Payments</a></li>
                            </ul>
                        </li>
                    @endif
                @endif

                @if ($canAccessProductionCosting)
                    <h2
                        class="-mx-4 mb-1 flex items-center bg-white-light/30 py-3 px-7 font-extrabold uppercase dark:bg-dark dark:bg-opacity-[0.08]">
                        <svg class="hidden h-5 w-4 flex-none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Costs & Expenses</span>
                    </h2>

                    {{-- Production & Costing --}}
                    <li class="menu nav-item">
                        <button type="button" class="nav-link group"
                            :class="{ 'active': activeDropdown === 'production' }"
                            @click="activeDropdown === 'production' ? activeDropdown = null : activeDropdown = 'production'">
                            <div class="flex items-center">
                                <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                    viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M12 2C10.8954 2 10 2.89543 10 4V8C10 9.10457 10.8954 10 12 10C13.1046 10 14 9.10457 14 8V4C14 2.89543 13.1046 2 12 2Z"
                                        fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M6 12C4.34315 12 3 13.3431 3 15V19C3 20.6569 4.34315 22 6 22H18C19.6569 22 21 20.6569 21 19V15C21 13.3431 19.6569 12 18 12H6Z"
                                        fill="currentColor"></path>
                                </svg>
                                <span
                                    class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Production
                                    &amp; Costing</span>
                            </div>
                            <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'production' }">
                                <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                        </button>
                        <ul x-cloak="" x-show="activeDropdown === 'production'" x-collapse=""
                            class="sub-menu text-gray-500">
                            <li><a href="{{ route('backoffice.daily-production-costs') }}" @class(['active' => request()->routeIs('backoffice.daily-production-costs')])>Daily Production Cost</a></li>
                            <li><a href="{{ route('backoffice.expense-categories') }}" @class(['active' => request()->routeIs('backoffice.expense-categories')])>Expense Categories</a></li>
                            <li><a href="{{ route('backoffice.expenses') }}" @class(['active' => request()->routeIs('backoffice.expenses')])>Expense Tracking</a></li>
                            <li><a href="{{ route('backoffice.net-revenue') }}" @class(['active' => request()->routeIs('backoffice.net-revenue')])>Net Revenue</a></li>
                        </ul>
                    </li>
                @endif
                @if ($canAccessMaintenance)
                {{-- Maintenance Management --}}
                <li class="menu nav-item">
                    <button type="button" class="nav-link group"
                        :class="{ 'active': activeDropdown === 'maintenance' }"
                        @click="activeDropdown === 'maintenance' ? activeDropdown = null : activeDropdown = 'maintenance'">
                        <div class="flex items-center">
                            <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M14.5 6.5C14.5 5.11929 13.3807 4 12 4C10.6193 4 9.5 5.11929 9.5 6.5V9H7C5.89543 9 5 9.89543 5 11V18C5 19.1046 5.89543 20 7 20H17C18.1046 20 19 19.1046 19 18V11C19 9.89543 18.1046 9 17 9H14.5V6.5Z"
                                    fill="currentColor"></path>
                                <path
                                    d="M10 14C10 13.4477 10.4477 13 11 13H13C13.5523 13 14 13.4477 14 14V16C14 16.5523 13.5523 17 13 17H11C10.4477 17 10 16.5523 10 16V14Z"
                                    fill="currentColor"></path>
                            </svg>
                            <span
                                class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Maintenance
                                MGT</span>
                        </div>
                        <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'maintenance' }">
                            <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </button>
                    <ul x-cloak="" x-show="activeDropdown === 'maintenance'" x-collapse=""
                        class="sub-menu text-gray-500">
                        <li><a href="{{ route('backoffice.maintenance.requests') }}" @class(['active' => request()->routeIs('backoffice.maintenance.requests')])>Maintenance Requests</a></li>
                    </ul>
                </li>
                @endif

                <h2
                    class="-mx-4 mb-1 flex items-center bg-white-light/30 py-3 px-7 font-extrabold uppercase dark:bg-dark dark:bg-opacity-[0.08]">
                    <svg class="hidden h-5 w-4 flex-none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Summary & Reporting</span>
                </h2>
                {{-- Reports --}}
                <li class="menu nav-item">
                    <button type="button" class="nav-link group"
                        :class="{ 'active': activeDropdown === 'reports' }"
                        @click="activeDropdown === 'reports' ? activeDropdown = null : activeDropdown = 'reports'">
                        <div class="flex items-center">
                            <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M6 2H14L20 8V20C20 21.1046 19.1046 22 18 22H6C4.89543 22 4 21.1046 4 20V4C4 2.89543 4.89543 2 6 2Z"
                                    fill="currentColor"></path>
                                <path d="M14 2V8H20" fill="currentColor"></path>
                            </svg>
                            <span
                                class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Reports</span>
                        </div>
                        <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'reports' }">
                            <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </button>
                    <ul x-cloak="" x-show="activeDropdown === 'reports'" x-collapse=""
                        class="sub-menu text-gray-500">
                        <li><a href="{{ route('backoffice.reports') }}" @class(['active' => request()->routeIs('backoffice.reports')])>Reports Overview</a></li>
                        <li><a href="{{ route('backoffice.reports.sales') }}" @class(['active' => request()->routeIs('backoffice.reports.sales')])>Sales Report</a></li>
                        <li><a href="{{ route('backoffice.reports.inventory') }}" @class(['active' => request()->routeIs('backoffice.reports.inventory')])>Inventory Report</a></li>
                        <li><a href="{{ route('backoffice.reports.finance') }}" @class(['active' => request()->routeIs('backoffice.reports.finance')])>Finance Report</a></li>
                        <li><a href="{{ route('backoffice.reports.maintenance') }}" @class(['active' => request()->routeIs('backoffice.reports.maintenance')])>Maintenance Report</a></li>
                        <li><a href="{{ route('backoffice.reports.production-costs') }}" @class(['active' => request()->routeIs('backoffice.reports.production-costs')])>Production Cost Report</a>
                        </li>
                        <li><a href="{{ route('backoffice.reports.cash-register') }}" @class(['active' => request()->routeIs('backoffice.reports.cash-register')])>Cash Register Report</a></li>
                    </ul>
                </li>

                @if (auth()->user()?->isSuperAdmin())
                    {{-- Audit & Activity Logs --}}
                    <li class="menu nav-item">
                        <button type="button" class="nav-link group"
                            :class="{ 'active': activeDropdown === 'logs' }"
                            @click="activeDropdown === 'logs' ? activeDropdown = null : activeDropdown = 'logs'">
                            <div class="flex items-center">
                                <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                    viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.5"
                                        d="M4 5C4 3.89543 4.89543 3 6 3H18C19.1046 3 20 3.89543 20 5V19C20 20.1046 19.1046 21 18 21H6C4.89543 21 4 20.1046 4 19V5Z"
                                        fill="currentColor"></path>
                                    <path d="M8 8H16M8 12H16M8 16H12" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round"></path>
                                </svg>
                                <span
                                    class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Activity &amp; Audit</span>
                            </div>
                            <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'logs' }">
                                <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                        </button>
                        <ul x-cloak="" x-show="activeDropdown === 'logs'" x-collapse=""
                            class="sub-menu text-gray-500">
                            <li><a href="{{ route('backoffice.logs.activity') }}" @class(['active' => request()->routeIs('backoffice.logs.activity')])>Activity Logs</a></li>
                            <li><a href="{{ route('backoffice.logs.audit') }}" @class(['active' => request()->routeIs('backoffice.logs.audit')])>Audit Logs</a></li>
                        </ul>
                    </li>
                @endif

                <h2
                    class="-mx-4 mb-1 flex items-center bg-white-light/30 py-3 px-7 font-extrabold uppercase dark:bg-dark dark:bg-opacity-[0.08]">
                    <svg class="hidden h-5 w-4 flex-none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Website Management</span>
                </h2>
                {{-- Website Management --}}
                <li class="menu nav-item">
                    <button type="button" class="nav-link group"
                        :class="{ 'active': activeDropdown === 'website' }"
                        @click="activeDropdown === 'website' ? activeDropdown = null : activeDropdown = 'website'">
                        <div class="flex items-center">
                            <svg class="shrink-0 group-hover:!text-primary" width="20" height="20"
                                viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2Z"
                                    fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8 12L10.5 14.5L16 9"
                                    fill="currentColor"></path>
                            </svg>
                            <span
                                class="text-black ltr:pl-3 rtl:pr-3 dark:text-[#506690] dark:group-hover:text-white-dark">Website Management</span>
                        </div>
                        <div class="rtl:rotate-180" :class="{ '!rotate-90': activeDropdown === 'website' }">
                            <svg width="16" height="16" viewbox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                    </button>
                    <ul x-cloak="" x-show="activeDropdown === 'website'" x-collapse=""
                        class="sub-menu text-gray-500">
                        <li><a href="{{ route('website.home') }}" @class(['active' => request()->routeIs('website.home')])>Public Homepage</a></li>
                        <li><a href="{{ route('website.about') }}" @class(['active' => request()->routeIs('website.about')])>About Page</a></li>
                        <li><a href="{{ route('website.branches') }}" @class(['active' => request()->routeIs('website.branches', 'website.branch.show')])>Branches Page</a></li>
                        <li><a href="{{ route('website.gallery') }}" @class(['active' => request()->routeIs('website.gallery')])>Gallery Page</a></li>
                        <li><a href="{{ route('website.events') }}" @class(['active' => request()->routeIs('website.events', 'website.events.show')])>Events Page</a></li>
                        <li><a href="{{ route('website.careers') }}" @class(['active' => request()->routeIs('website.careers')])>Careers Page</a></li>
                        <li><a href="{{ route('website.contact') }}" @class(['active' => request()->routeIs('website.contact')])>Contact Page</a></li>
                        <li><a href="{{ route('website.reviews') }}" @class(['active' => request()->routeIs('website.reviews')])>Reviews Page</a></li>
                        @if (auth()->user()?->isSuperAdmin())
                            <li><a href="{{ route('backoffice.website.pages') }}" @class(['active' => request()->routeIs('backoffice.website.pages')])>Page Content</a></li>
                        @endif
                        <li><a href="{{ route('backoffice.website.events') }}" @class(['active' => request()->routeIs('backoffice.website.events')])>Events Manager</a></li>
                        <li><a href="{{ route('backoffice.website.gallery') }}" @class(['active' => request()->routeIs('backoffice.website.gallery')])>Gallery Manager</a></li>
                        <li><a href="{{ route('backoffice.website.careers') }}" @class(['active' => request()->routeIs('backoffice.website.careers')])>Careers Manager</a></li>
                        <li><a href="{{ route('backoffice.website.testimonials') }}" @class(['active' => request()->routeIs('backoffice.website.testimonials')])>Testimonials</a></li>
                        <li><a href="{{ route('backoffice.website.reviews') }}" @class(['active' => request()->routeIs('backoffice.website.reviews')])>Reviews &amp; Ratings</a></li>
                        <li><a href="{{ route('backoffice.website.contact-messages') }}" @class(['active' => request()->routeIs('backoffice.website.contact-messages')])>Contact Messages</a></li>
                        @if (auth()->user()?->isSuperAdmin())
                            <li><a href="{{ route('backoffice.website.settings') }}" @class(['active' => request()->routeIs('backoffice.website.settings')])>Website Settings</a></li>
                        @endif
                    </ul>
                </li>

            </ul>

            <div class="flex-none border-t border-slate-100 bg-white p-3 dark:border-white/10 dark:bg-[#0e1726]">
                <form method="POST" action="{{ route('logout') }}" class="mb-3">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-2 rounded-lg border border-danger/20 bg-danger/10 px-3 py-2.5 text-sm font-bold text-danger transition hover:bg-danger hover:text-white focus:outline-none focus:ring-2 focus:ring-danger/30">
                        <svg class="h-4.5 w-4.5 shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 3H19C20.1046 3 21 3.89543 21 5V19C21 20.1046 20.1046 21 19 21H15"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M15 12H3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>

                <div class="rounded-lg border border-white-light/30 bg-white/90 px-3 py-3 shadow-sm dark:bg-[#07121f]">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0 flex flex-col">
                            <span class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-white-light/60">Branch</span>
                            <span class="truncate text-sm font-semibold text-dark dark:text-white-dark">
                                {{ $currentBranch->name ?? 'No branch selected' }}
                            </span>
                        </div>
                        @if ($currentBranch)
                            <span class="inline-flex flex-none items-center gap-1 rounded-full bg-emerald-100 px-2 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
                                Active
                            </span>
                        @endif
                    </div>
                    <div class="mt-3">
                        @if ($branches->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-white-light">No active branches available.</p>
                        @else
                            <form action="{{ route('backoffice.branch.select') }}" method="POST">
                                @csrf
                                <label for="sidebar-branch-selector" class="sr-only">Select branch</label>
                                <select id="sidebar-branch-selector" name="branch_id"
                                    onchange="this.form.submit()"
                                    class="form-input w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm outline-none focus:border-primary focus:ring-0 dark:border-[#25314a] dark:bg-[#0e1726] dark:text-white">
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}" @selected($branch->id == $currentBranch?->id)>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>
