<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\BranchStaff;
use App\Models\CareerApplication;
use App\Models\CareerOpening;
use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\DailyProductionCost;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GalleryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryItemStock;
use App\Models\InventoryLocation;
use App\Models\InventoryStockAdjustment;
use App\Models\InventoryStockTransfer;
use App\Models\MaintenanceRequest;
use App\Models\MenuItem;
use App\Models\MenuItemAdjustment;
use App\Models\Module;
use App\Models\ModuleStaff;
use App\Models\Payment;
use App\Models\ProductionDayLock;
use App\Models\Review;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WebsiteEvent;
use App\Models\WebsitePage;
use App\Models\WebsiteSetting;
use App\Observers\AuditableObserver;
use App\Support\AuditLogger;
use Illuminate\Support\Collection;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerAuditObservers();
        $this->registerActivityListeners();

        View::composer(['components.layouts.*', 'components.layout.*'], function ($view) {
            $user = auth()->user();
            $branches = Schema::hasTable('branches')
                ? ($user
                    ? $user->accessibleBranches()->get()
                    : Branch::where('is_active', true)->orderBy('id')->get())
                : new Collection();

            $currentBranch = null;
            $branchId = session('branch_id');

            if ($branchId && Schema::hasTable('branches') && (! $user || $user->canAccessBranch($branchId))) {
                $currentBranch = Branch::find($branchId);
            }

            if (!$currentBranch && $branches->isNotEmpty()) {
                $currentBranch = $branches->first();
                session(['branch_id' => $currentBranch->id]);
            }

            $view->with([
                'branches' => $branches,
                'currentBranch' => $currentBranch,
            ]);
        });
    }

    private function registerAuditObservers(): void
    {
        foreach ([
            Branch::class,
            BranchStaff::class,
            CareerApplication::class,
            CareerOpening::class,
            CashRegister::class,
            CashRegisterTransaction::class,
            Category::class,
            ContactMessage::class,
            Customer::class,
            CustomerDebt::class,
            DailyProductionCost::class,
            Expense::class,
            ExpenseCategory::class,
            GalleryItem::class,
            InventoryCategory::class,
            InventoryItem::class,
            InventoryItemStock::class,
            InventoryLocation::class,
            InventoryStockAdjustment::class,
            InventoryStockTransfer::class,
            MaintenanceRequest::class,
            MenuItem::class,
            MenuItemAdjustment::class,
            Module::class,
            ModuleStaff::class,
            Payment::class,
            ProductionDayLock::class,
            Review::class,
            Sale::class,
            SaleItem::class,
            Supplier::class,
            Testimonial::class,
            User::class,
            WebsiteEvent::class,
            WebsitePage::class,
            WebsiteSetting::class,
        ] as $model) {
            $model::observe(AuditableObserver::class);
        }
    }

    private function registerActivityListeners(): void
    {
        Event::listen(Login::class, fn (Login $event) => AuditLogger::activity('auth.login', 'User signed in', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
        ]));

        Event::listen(Logout::class, fn (Logout $event) => AuditLogger::activity('auth.logout', 'User signed out', [
            'user_id' => $event->user?->id,
            'email' => $event->user?->email,
        ]));
    }
}
