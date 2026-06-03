<?php

use App\Livewire\Backoffice\Branches\Index as BranchesIndex;
use App\Livewire\Backoffice\Categories\Index as CategoriesIndex;
use App\Livewire\Backoffice\Customers\Index as CustomersIndex;
use App\Livewire\Backoffice\Customers\History as CustomerHistory;
use App\Livewire\Backoffice\Dashboards\Home as DashboardHome;
use App\Livewire\Backoffice\Dashboards\Quantitative as DashboardQuantitative;
use App\Livewire\Backoffice\Dashboards\Financial as DashboardFinancial;
use App\Livewire\Backoffice\Dashboards\Analytical as DashboardAnalytical;
use App\Livewire\Backoffice\DailyProductionCosts\Index as DailyProductionCostsIndex;
use App\Livewire\Backoffice\ExpenseCategories\Index as ExpenseCategoriesIndex;
use App\Livewire\Backoffice\Expenses\Index as ExpensesIndex;
use App\Livewire\Backoffice\InventoryCategories\Index as InventoryCategoriesIndex;
use App\Livewire\Backoffice\InventoryDashboard\Index as InventoryDashboardIndex;
use App\Livewire\Backoffice\InventoryItemStocks\Index as InventoryItemStocksIndex;
use App\Livewire\Backoffice\InventoryItems\Index as InventoryItemsIndex;
use App\Livewire\Backoffice\InventoryLocations\Index as InventoryLocationsIndex;
use App\Livewire\Backoffice\InventoryPurchaseOrderRequests\Index as InventoryPurchaseOrderRequestsIndex;
use App\Livewire\Backoffice\InventoryStockAdjustments\Index as InventoryStockAdjustmentsIndex;
use App\Livewire\Backoffice\InventoryStockMovements\Index as InventoryStockMovementsIndex;
use App\Livewire\Backoffice\InventoryStockTransfers\Index as InventoryStockTransfersIndex;
use App\Livewire\Backoffice\Logs\ActivityLogs;
use App\Livewire\Backoffice\Logs\AuditLogs;
use App\Livewire\Backoffice\MenuItemAdjustments\Index as MenuItemAdjustmentsIndex;
use App\Livewire\Backoffice\MenuItemStockRequests\Index as MenuItemStockRequestsIndex;
use App\Livewire\Backoffice\MenuItems\Index as MenuItemsIndex;
use App\Livewire\Backoffice\Modules\Index as ModulesIndex;
use App\Livewire\Backoffice\ModuleStaff\Index as ModuleStaffIndex;
use App\Livewire\Backoffice\BranchStaff\Index as BranchStaffIndex;
use App\Livewire\Backoffice\NetRevenue\Index as NetRevenueIndex;
use App\Livewire\Backoffice\Pos\DebtorsIndex as PosDebtorsIndex;
use App\Livewire\Backoffice\Pos\Index as PosIndex;
use App\Livewire\Backoffice\Pos\KitchenIndex as PosKitchenIndex;
use App\Livewire\Backoffice\Pos\RefundsIndex as PosRefundsIndex;
use App\Livewire\Backoffice\Pos\SalesIndex as PosSalesIndex;
use App\Livewire\Backoffice\Pos\SplitPaymentsIndex as PosSplitPaymentsIndex;
use App\Livewire\Backoffice\Pos\TransactionsIndex as PosTransactionsIndex;
use App\Livewire\Backoffice\Suppliers\Index as SuppliersIndex;
use App\Livewire\Backoffice\Staff\Index as StaffIndex;
use App\Livewire\Backoffice\Taxes\Index as TaxesIndex;
use App\Livewire\Backoffice\Discounts\Index as DiscountsIndex;
use App\Livewire\Backoffice\Users\Index;
use App\Livewire\Backoffice\Permissions\Index as PermissionsIndex;
use App\Livewire\Backoffice\Roles\Index as RolesIndex;
use App\Livewire\Backoffice\Website\ContactMessagesIndex;
use App\Livewire\Backoffice\Website\CareersIndex as WebsiteCareersIndex;
use App\Livewire\Backoffice\Website\EventsIndex as WebsiteEventsIndex;
use App\Livewire\Backoffice\Website\GalleryIndex as WebsiteGalleryIndex;
use App\Livewire\Backoffice\Website\PagesIndex as WebsitePagesIndex;
use App\Livewire\Backoffice\Website\ReviewsIndex;
use App\Livewire\Backoffice\Website\SettingsIndex;
use App\Livewire\Backoffice\Website\TestimonialsIndex;
use App\Livewire\Backoffice\Maintenance\RequestsIndex as MaintenanceRequestsIndex;
use App\Livewire\Backoffice\Reports\Index as ReportsIndex;
use App\Livewire\Backoffice\Reports\SalesReport;
use App\Livewire\Backoffice\Reports\InventoryReport;
use App\Livewire\Backoffice\Reports\FinanceReport;
use App\Livewire\Backoffice\Reports\MaintenanceReport;
use App\Livewire\Backoffice\Reports\ProductionCostsReport;
use App\Livewire\Backoffice\Reports\CashRegisterReport;
use App\Http\Controllers\Backoffice\ReportExportController;
use App\Livewire\Website\About as WebsiteAbout;
use App\Livewire\Website\Branches as WebsiteBranches;
use App\Livewire\Website\BranchShow as WebsiteBranchShow;
use App\Livewire\Website\Careers as WebsiteCareers;
use App\Livewire\Website\ContactPage;
use App\Livewire\Website\EventShow as WebsiteEventShow;
use App\Livewire\Website\Events as WebsiteEvents;
use App\Livewire\Website\Gallery as WebsiteGallery;
use App\Livewire\Website\Home as WebsiteHome;
use App\Livewire\Website\MenuItemShow as WebsiteMenuItemShow;
use App\Livewire\Website\Reviews as WebsiteReviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', WebsiteHome::class)
    ->name('website.home');

Route::get('/contact', ContactPage::class)
    ->name('website.contact');

Route::get('/reviews', WebsiteReviews::class)
    ->name('website.reviews');

Route::get('/about', WebsiteAbout::class)
    ->name('website.about');

Route::get('/branches', WebsiteBranches::class)
    ->name('website.branches');

Route::get('/branches/{slug}', WebsiteBranchShow::class)
    ->name('website.branch.show');

Route::get('/gallery/{category?}', WebsiteGallery::class)
    ->name('website.gallery');

Route::get('/events', WebsiteEvents::class)
    ->name('website.events');

Route::get('/events/{slug}', WebsiteEventShow::class)
    ->name('website.events.show');

Route::get('/menu/{slug}', WebsiteMenuItemShow::class)
    ->name('website.menu.show');

Route::get('/careers', WebsiteCareers::class)
    ->name('website.careers');

Route::middleware(['auth', 'log.backoffice'])->prefix('backoffice')->group(function () {
Route::get('home', DashboardHome::class)
    ->middleware('role:Super Admin|Branch Manager|POS Operator|Accountant')
    ->name('dashboard');
Route::get('dashboards/quantitative', DashboardQuantitative::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.dashboards.quantitative');
Route::get('dashboards/financial', DashboardFinancial::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.dashboards.financial');
Route::get('dashboards/analytical', DashboardAnalytical::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.dashboards.analytical');
Route::get('users', Index::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.users');
Route::get('staff', StaffIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.staff');
Route::get('branches', BranchesIndex::class)
    ->middleware('role:Super Admin')
    ->name('backoffice.branches');

Route::post('branch/select', function (Request $request) {
    $request->validate([
        'branch_id' => 'required|exists:branches,id',
    ]);

    abort_unless($request->user()?->canAccessBranch($request->input('branch_id')), 403);

    session(['branch_id' => (int) $request->input('branch_id')]);

    return back();
})->name('backoffice.branch.select');

Route::get('modules', ModulesIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.modules');
Route::get('module-staff', ModuleStaffIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.module-staff');
Route::get('branch-staff', BranchStaffIndex::class)
    ->middleware('role:Super Admin')
    ->name('backoffice.branch-staff');

Route::get('customers', CustomersIndex::class)
    ->name('backoffice.customers');
Route::get('customers/history', CustomerHistory::class)
    ->name('backoffice.customers.history');

Route::get('categories', CategoriesIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.categories');
Route::get('menu-items', MenuItemsIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.menu-items');
Route::get('menu-item-adjustments', MenuItemAdjustmentsIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.menu-item-adjustments');
Route::get('menu-item-stock-requests', MenuItemStockRequestsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager|POS Operator')
    ->name('backoffice.menu-item-stock-requests');
Route::get('suppliers', SuppliersIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.suppliers');
Route::get('inventory-categories', InventoryCategoriesIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.inventory-categories');
Route::get('inventory-items', InventoryItemsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.inventory-items');
Route::get('inventory-dashboard', InventoryDashboardIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.inventory-dashboard');
Route::get('inventory-locations', InventoryLocationsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.inventory-locations');
Route::get('inventory-item-stocks', InventoryItemStocksIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.inventory-item-stocks');
Route::get('stock-adjustments', InventoryStockAdjustmentsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.stock-adjustments');
Route::get('stock-movements', InventoryStockMovementsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.stock-movements');
Route::get('stock-transfers', InventoryStockTransfersIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager')
    ->name('backoffice.stock-transfers');
Route::get('purchase-order-requests', InventoryPurchaseOrderRequestsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Inventory Manager|Accountant')
    ->name('backoffice.purchase-order-requests');
Route::get('daily-production-costs', DailyProductionCostsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.daily-production-costs');
Route::get('expense-categories', ExpenseCategoriesIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.expense-categories');
Route::get('expenses', ExpensesIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.expenses');
Route::get('net-revenue', NetRevenueIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.net-revenue');
Route::get('website/testimonials', TestimonialsIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.website.testimonials');
Route::get('website/events', WebsiteEventsIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.website.events');
Route::get('website/gallery', WebsiteGalleryIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.website.gallery');
Route::get('website/careers', WebsiteCareersIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.website.careers');
Route::get('website/pages', WebsitePagesIndex::class)
    ->middleware('role:Super Admin')
    ->name('backoffice.website.pages');
Route::get('website/reviews', ReviewsIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.website.reviews');
Route::get('website/contact-messages', ContactMessagesIndex::class)
    ->middleware('role:Super Admin|Branch Manager')
    ->name('backoffice.website.contact-messages');
Route::get('website/settings', SettingsIndex::class)
    ->middleware('role:Super Admin')
    ->name('backoffice.website.settings');
Route::get('reports', ReportsIndex::class)
    ->name('backoffice.reports');
Route::get('reports/sales', SalesReport::class)
    ->name('backoffice.reports.sales');
Route::get('reports/inventory', InventoryReport::class)
    ->name('backoffice.reports.inventory');
Route::get('reports/finance', FinanceReport::class)
    ->name('backoffice.reports.finance');
Route::get('reports/maintenance', MaintenanceReport::class)
    ->name('backoffice.reports.maintenance');
Route::get('reports/production-costs', ProductionCostsReport::class)
    ->name('backoffice.reports.production-costs');
Route::get('reports/cash-register', CashRegisterReport::class)
    ->name('backoffice.reports.cash-register');
Route::get('reports/{report}/pdf', ReportExportController::class)
    ->whereIn('report', ['overview', 'sales', 'inventory', 'finance', 'maintenance', 'production-costs', 'cash-register'])
    ->name('backoffice.reports.pdf');
Route::get('logs/activity', ActivityLogs::class)
    ->middleware('role:Super Admin')
    ->name('backoffice.logs.activity');
Route::get('logs/audit', AuditLogs::class)
    ->middleware('role:Super Admin')
    ->name('backoffice.logs.audit');
Route::get('maintenance/requests', MaintenanceRequestsIndex::class)
    ->name('backoffice.maintenance.requests');
Route::get('roles', RolesIndex::class)
    ->middleware('role:Super Admin')
    ->name('backoffice.roles');
Route::get('permissions', PermissionsIndex::class)
    ->middleware('role:Super Admin')
    ->name('backoffice.permissions');
Route::get('pos', PosIndex::class)
    ->middleware('role:Super Admin|Branch Manager|POS Operator')
    ->name('backoffice.pos');
Route::get('pos/sales', PosSalesIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.pos.sales');
Route::get('pos/kitchen', PosKitchenIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Kitchen Staff')
    ->name('backoffice.pos.kitchen');
Route::get('pos/transactions', PosTransactionsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.pos.transactions');
Route::get('pos/refunds', PosRefundsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.pos.refunds');
Route::get('pos/debtors', PosDebtorsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.pos.debtors');
Route::get('pos/split-payments', PosSplitPaymentsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.pos.split-payments');
Route::get('pos/taxes', TaxesIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.pos.taxes');
Route::get('pos/discounts', DiscountsIndex::class)
    ->middleware('role:Super Admin|Branch Manager|Accountant')
    ->name('backoffice.pos.discounts');

Route::get('profile', \App\Livewire\Backoffice\UserProfile::class)
    ->name('backoffice.profile');
});
