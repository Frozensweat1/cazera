<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Livewire\Backoffice\Reports\CashRegisterReport;
use App\Livewire\Backoffice\Reports\FinanceReport;
use App\Livewire\Backoffice\Reports\Index as ReportsIndex;
use App\Livewire\Backoffice\Reports\InventoryReport;
use App\Livewire\Backoffice\Reports\MaintenanceReport;
use App\Livewire\Backoffice\Reports\ProductionCostsReport;
use App\Livewire\Backoffice\Reports\SalesReport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportExportController extends Controller
{
    /**
     * @var array<string, array{class: class-string, title: string}>
     */
    private const REPORTS = [
        'overview' => ['class' => ReportsIndex::class, 'title' => 'Reports Overview'],
        'sales' => ['class' => SalesReport::class, 'title' => 'Sales Report'],
        'inventory' => ['class' => InventoryReport::class, 'title' => 'Inventory Report'],
        'finance' => ['class' => FinanceReport::class, 'title' => 'Finance Report'],
        'maintenance' => ['class' => MaintenanceReport::class, 'title' => 'Maintenance Report'],
        'production-costs' => ['class' => ProductionCostsReport::class, 'title' => 'Production Cost Report'],
        'cash-register' => ['class' => CashRegisterReport::class, 'title' => 'Cash Register Report'],
    ];

    public function __invoke(Request $request, string $report): Response
    {
        abort_unless(array_key_exists($report, self::REPORTS), 404);

        $component = app(self::REPORTS[$report]['class']);

        if (method_exists($component, 'mount')) {
            $component->mount();
        }

        foreach (['filterBranch', 'filterModule', 'dateFrom', 'dateTo'] as $property) {
            if (property_exists($component, $property) && $request->filled($property)) {
                $component->{$property} = $request->input($property);
            }
        }

        /** @var View $reportView */
        $reportView = $component->render();

        return response()->view('backoffice.reports.export', [
            'title' => self::REPORTS[$report]['title'],
            'content' => $reportView->render(),
            'generatedAt' => now(),
        ]);
    }
}
