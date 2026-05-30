<?php

namespace App\Livewire\Backoffice\Customers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public string $search = '';

    public string $customerType = '';

    public string $status = '';

    public string $saleStatus = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?int $selectedCustomerId = null;

    public function mount(): void
    {
        $customer = request()->integer('customer');

        if ($customer && Customer::query()->whereKey($customer)->exists()) {
            $this->selectedCustomerId = $customer;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage('customersPage');
    }

    public function updatedCustomerType(): void
    {
        $this->resetPage('customersPage');
    }

    public function updatedStatus(): void
    {
        $this->resetPage('customersPage');
    }

    public function updatedSaleStatus(): void
    {
        $this->resetPage('historyPage');
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage('historyPage');
    }

    public function updatedDateTo(): void
    {
        $this->resetPage('historyPage');
    }

    public function selectCustomer(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
        $this->resetPage('historyPage');
    }

    public function clearHistoryFilters(): void
    {
        $this->reset([
            'saleStatus',
            'dateFrom',
            'dateTo',
        ]);

        $this->resetPage('historyPage');
    }

    public function render()
    {
        $customers = Customer::query()
            ->with('branch')
            ->when($this->search, fn (Builder $query) => $query
                ->where(fn (Builder $query) => $query
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->customerType, fn (Builder $query) => $query->where('customer_type', $this->customerType))
            ->when($this->status, fn (Builder $query) => $query->where('status', $this->status))
            ->orderByDesc('last_order_at')
            ->orderBy('name')
            ->paginate(10, pageName: 'customersPage');

        $selectedCustomer = $this->selectedCustomerId
            ? Customer::query()->with('branch')->find($this->selectedCustomerId)
            : null;

        $salesQuery = Sale::query()
            ->with(['branch', 'module', 'items', 'payments'])
            ->where('customer_id', $this->selectedCustomerId)
            ->when($this->saleStatus, fn (Builder $query) => $query->where('status', $this->saleStatus))
            ->when($this->dateFrom, fn (Builder $query) => $query->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn (Builder $query) => $query->whereDate('sale_date', '<=', $this->dateTo));

        $historyTotals = [
            'orders' => $selectedCustomer ? (clone $salesQuery)->count() : 0,
            'sales' => $selectedCustomer ? (clone $salesQuery)->sum('total') : 0,
            'paid' => $selectedCustomer
                ? Payment::query()
                    ->whereHas('sale', fn (Builder $query) => $query->where('customer_id', $selectedCustomer->id))
                    ->when($this->dateFrom, fn (Builder $query) => $query->whereDate('paid_at', '>=', $this->dateFrom))
                    ->when($this->dateTo, fn (Builder $query) => $query->whereDate('paid_at', '<=', $this->dateTo))
                    ->sum('amount')
                : 0,
            'debt' => $selectedCustomer?->total_debt ?? 0,
        ];

        return view('livewire.backoffice.customers.history', [
            'customers' => $customers,
            'selectedCustomer' => $selectedCustomer,
            'sales' => $selectedCustomer
                ? $salesQuery->latest('sale_date')->paginate(8, pageName: 'historyPage')
                : collect(),
            'historyTotals' => $historyTotals,
        ]);
    }
}
