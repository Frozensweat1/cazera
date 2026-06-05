<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use App\Models\Customer;
use App\Models\MenuItemAdjustment;
use App\Models\Payment;
use App\Models\Sale;
use App\Support\WebsiteContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class SalesIndex extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterStatus = '';
    public $receiptSaleId;
    public $paymentSaleId;
    public $payment_method = 'cash';
    public $payment_amount = 0;
    public $payment_reference;
    public $refundSaleId;
    public $refund_method = 'cash';
    public $refund_amount = 0;
    public $refund_reason;

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));

        return view('livewire.backoffice.pos.sales-index', [
            'sales' => Sale::with(['branch', 'customer', 'module', 'creator', 'payments'])
                ->accessible()
                ->where('status', '!=', 'refunded')
                ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterStatus, fn($query) => $query->where('status', $this->filterStatus))
                ->when($this->search, fn($query) => $query->where(function ($query) {
                    $query->where('sale_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn($query) => $query->where('name', 'like', "%{$this->search}%"));
                }))
                ->latest('sale_date')
                ->paginate(15),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'receiptSale' => $this->receiptSaleId
                ? Sale::with(['branch', 'module', 'customer', 'creator', 'items', 'payments.receiver'])->find($this->receiptSaleId)
                : null,
            'paymentSale' => $this->paymentSaleId
                ? Sale::with(['customer', 'module'])->find($this->paymentSaleId)
                : null,
            'refundSale' => $this->refundSaleId
                ? Sale::with(['customer', 'module'])->find($this->refundSaleId)
                : null,
            'receiptSettings' => $this->receiptSettings(),
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
        $this->resetPage();
    }

    public function updatedFilterModule(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function viewReceipt($saleId): void
    {
        $sale = Sale::accessible()->findOrFail($saleId);
        $this->receiptSaleId = $sale->id;
        $this->dispatch('open-modal', 'sales-receipt-modal');
    }

    public function openPayment($saleId): void
    {
        $sale = Sale::accessible()->findOrFail($saleId);
        abort_if((float) $sale->remaining_balance <= 0, 422, 'This sale has no outstanding balance.');

        $this->paymentSaleId = $sale->id;
        $this->payment_method = 'cash';
        $this->payment_amount = (float) $sale->remaining_balance;
        $this->payment_reference = null;
        $this->dispatch('open-modal', 'sales-payment-modal');
    }

    public function collectPayment(): void
    {
        $this->validate([
            'paymentSaleId' => 'required|exists:sales,id',
            'payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank_transfer', 'wallet'])],
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () {
            $sale = Sale::accessible()->lockForUpdate()->findOrFail($this->paymentSaleId);
            $amount = round((float) $this->payment_amount, 2);

            abort_if($amount > (float) $sale->remaining_balance, 422, 'Payment cannot exceed outstanding balance.');

            $register = $this->openRegisterFor($sale);
            $this->recordPayment($sale, $register, $this->payment_method, $amount, $this->payment_reference);

            $paid = round((float) $sale->paid_amount + $amount, 2);
            $remaining = round((float) $sale->total - $paid, 2);

            $sale->update([
                'paid_amount' => $paid,
                'remaining_balance' => max(0, $remaining),
                'is_debt' => $remaining > 0,
                'status' => $remaining <= 0 ? 'completed' : $sale->status,
                'completed_at' => $remaining <= 0 ? now() : $sale->completed_at,
            ]);
        });

        $this->dispatch('close-modal', 'sales-payment-modal');

        LivewireAlert::title('Payment Recorded')
            ->text('Outstanding balance has been updated.')
            ->success()
            ->show();
    }

    public function openRefund($saleId): void
    {
        $sale = Sale::accessible()->findOrFail($saleId);
        abort_if((float) $sale->paid_amount <= 0, 422, 'This sale has no paid amount to refund.');

        $this->refundSaleId = $sale->id;
        $this->refund_method = 'cash';
        $this->refund_amount = (float) $sale->paid_amount;
        $this->refund_reason = null;
        $this->dispatch('open-modal', 'sales-refund-modal');
    }

    public function processRefund(): void
    {
        $this->validate([
            'refundSaleId' => 'required|exists:sales,id',
            'refund_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank_transfer', 'wallet'])],
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () {
            $sale = Sale::accessible()
                ->with(['items.menuItem', 'customer'])
                ->lockForUpdate()
                ->findOrFail($this->refundSaleId);
            $amount = round((float) $this->refund_amount, 2);

            abort_if($amount > (float) $sale->paid_amount, 422, 'Refund cannot exceed paid amount.');

            $register = $this->openRegisterFor($sale);

            CashRegisterTransaction::create([
                'cash_register_id' => $register->id,
                'branch_id' => $sale->branch_id,
                'module_id' => $sale->module_id,
                'sale_id' => $sale->id,
                'performed_by' => auth()->id(),
                'type' => 'refund',
                'amount' => -1 * $amount,
                'notes' => $this->refund_reason ?: 'Refund for sale ' . $sale->sale_number,
                'transaction_date' => now(),
            ]);

            $register->addExpectedBalanceForTransaction('refund', -1 * $amount);

            Payment::create([
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'module_id' => $sale->module_id,
                'cash_register_id' => $register->id,
                'received_by' => auth()->id(),
                'method' => $this->refund_method,
                'amount' => $amount,
                'transaction_reference' => null,
                'status' => 'refunded',
                'notes' => $this->refund_reason,
                'paid_at' => now(),
            ]);

            foreach ($sale->items as $item) {
                if (! $item->menuItem?->is_trackable) {
                    continue;
                }

                $menuItem = $item->menuItem()->lockForUpdate()->first();

                if (! $menuItem?->is_trackable) {
                    continue;
                }

                $before = (int) $menuItem->quantity;
                $after = $before + (int) $item->qty;

                $menuItem->update([
                    'quantity' => $after,
                    'status' => $menuItem->status === 'out_of_stock' && $after > 0 ? 'available' : $menuItem->status,
                ]);

                MenuItemAdjustment::create([
                    'branch_id' => $sale->branch_id,
                    'module_id' => $sale->module_id,
                    'menu_item_id' => $item->menu_item_id,
                    'sale_id' => $sale->id,
                    'performed_by' => auth()->id(),
                    'type' => 'refund',
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'change_qty' => (int) $item->qty,
                    'reference_no' => $sale->sale_number,
                    'reason' => 'Refund return',
                    'transaction_date' => now(),
                ]);
            }

            $paid = round((float) $sale->paid_amount - $amount, 2);
            $remaining = round((float) $sale->total - $paid, 2);

            $sale->update([
                'paid_amount' => max(0, $paid),
                'remaining_balance' => max(0, $remaining),
                'is_debt' => false,
                'status' => 'refunded',
            ]);

            if ($sale->customer) {
                Customer::whereKey($sale->customer_id)->update([
                    'total_spent' => DB::raw('GREATEST(COALESCE(total_spent, 0) - ' . $amount . ', 0)'),
                ]);
            }
        });

        $this->dispatch('close-modal', 'sales-refund-modal');

        LivewireAlert::title('Refund Processed')
            ->text('Refund has been recorded successfully.')
            ->success()
            ->show();
    }

    protected function openRegisterFor(Sale $sale): CashRegister
    {
        $register = CashRegister::where('branch_id', $sale->branch_id)
            ->where('module_id', $sale->module_id)
            ->where('is_open', true)
            ->latest('opened_at')
            ->first();

        return $register ?: CashRegister::create([
            'branch_id' => $sale->branch_id,
            'module_id' => $sale->module_id,
            'opened_by' => auth()->id(),
            'name' => 'Auto-opened POS register',
        ]);
    }

    protected function recordPayment(Sale $sale, CashRegister $register, string $method, float $amount, ?string $reference = null): void
    {
        CashRegisterTransaction::create([
            'cash_register_id' => $register->id,
            'branch_id' => $sale->branch_id,
            'module_id' => $sale->module_id,
            'sale_id' => $sale->id,
            'performed_by' => auth()->id(),
            'type' => 'sale',
            'amount' => $amount,
            'notes' => 'Balance payment for sale ' . $sale->sale_number,
            'transaction_date' => now(),
        ]);

        $register->addExpectedBalanceForTransaction('sale', $amount);

        Payment::create([
            'sale_id' => $sale->id,
            'branch_id' => $sale->branch_id,
            'module_id' => $sale->module_id,
            'cash_register_id' => $register->id,
            'received_by' => auth()->id(),
            'method' => $method,
            'amount' => $amount,
            'transaction_reference' => $reference,
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    protected function receiptSettings(): array
    {
        $settings = WebsiteContent::settings();

        return [
            'business_name' => $settings?->business_name ?: config('app.name', 'Cazera'),
            'tagline' => $settings?->tagline,
            'address' => $settings?->address,
            'phone' => $settings?->phone,
            'email' => $settings?->email,
            'whatsapp' => $settings?->whatsapp,
        ];
    }
}
