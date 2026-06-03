<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class DebtorsIndex extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $paymentSaleId;
    public $payment_method = 'cash';
    public $payment_amount = 0;
    public $payment_reference = '';

    public function render()
    {
        $branchId = session('branch_id');
        $paymentSale = $this->paymentSaleId
            ? Sale::with(['customer', 'branch', 'module'])->accessible()->find($this->paymentSaleId)
            : null;

        return view('livewire.backoffice.pos.debtors-index', [
            'debtors' => Sale::with(['customer', 'branch', 'module', 'creator'])
                ->accessible()
                ->where('is_debt', true)
                ->where('status', '!=', 'refunded')
                ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->search, fn($query) => $query->where(function ($query) {
                    $query->where('sale_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
                }))
                ->latest('sale_date')
                ->paginate(15),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $branchId ?: null),
            'paymentSale' => $paymentSale,
        ]);
    }

    public function openPayment($saleId): void
    {
        $sale = Sale::accessible()
            ->where('is_debt', true)
            ->where('status', '!=', 'refunded')
            ->findOrFail($saleId);

        abort_if((float) $sale->remaining_balance <= 0, 422, 'This sale has no outstanding balance.');

        $this->paymentSaleId = $sale->id;
        $this->payment_method = 'cash';
        $this->payment_amount = (float) $sale->remaining_balance;
        $this->payment_reference = '';

        $this->dispatch('open-modal', 'debtor-payment-modal');
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
            $sale = Sale::accessible()
                ->where('is_debt', true)
                ->where('status', '!=', 'refunded')
                ->lockForUpdate()
                ->findOrFail($this->paymentSaleId);
            $amount = round((float) $this->payment_amount, 2);

            abort_if($amount > (float) $sale->remaining_balance, 422, 'Payment cannot exceed outstanding balance.');

            $register = $this->openRegisterFor($sale);
            $this->recordPayment($sale, $register, $this->payment_method, $amount, $this->payment_reference ?: null);

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

        $this->reset(['paymentSaleId', 'payment_reference']);
        $this->payment_method = 'cash';
        $this->payment_amount = 0;
        $this->dispatch('close-modal', 'debtor-payment-modal');

        LivewireAlert::title('Payment Recorded')
            ->text('The debtor balance has been updated.')
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
            'name' => 'Auto-opened debtor payment register',
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
            'notes' => 'Debt payment for sale ' . $sale->sale_number,
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

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
        $this->resetPage();
    }

    public function updatedFilterModule(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}
