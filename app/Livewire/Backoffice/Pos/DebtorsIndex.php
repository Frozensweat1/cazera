<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Sale;
use App\Support\SalePaymentRecorder;
use App\Support\SaleTableRelease;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class DebtorsIndex extends Component
{
    use HasBranchScope;
    use WithPagination;

    public $search = '';

    public $filterBranch = '';

    public $filterModule = '';

    public $paymentSaleId;

    public $payment_method = 'cash';

    public $payment_amount = 0;

    public $payment_reference = '';

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));
        $paymentSale = $this->paymentSaleId
            ? Sale::with(['customer', 'branch', 'modules'])->accessible()->find($this->paymentSaleId)
            : null;

        return view('livewire.backoffice.pos.debtors-index', [
            'debtors' => Sale::with(['customer', 'branch', 'modules', 'creator'])
                ->accessible()
                ->where('is_debt', true)
                ->where('status', '!=', 'refunded')
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->forModule($this->filterModule)
                ->when($this->search, fn ($query) => $query->where(function ($query) {
                    $query->where('sale_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
                }))
                ->latest('sale_date')
                ->paginate(15),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
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
        try {
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
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($this->paymentSaleId);
                $amount = round((float) $this->payment_amount, 2);

                if ($amount > (float) $sale->remaining_balance) {
                    throw ValidationException::withMessages([
                        'payment_amount' => 'Payment cannot exceed the outstanding balance.',
                    ]);
                }

                SalePaymentRecorder::recordCollection(
                    sale: $sale,
                    method: $this->payment_method,
                    amount: $amount,
                    reference: $this->payment_reference ?: null,
                    userId: (int) auth()->id(),
                    registerName: 'Auto-opened debtor payment register',
                    notes: 'Debt payment for sale '.$sale->sale_number,
                );

                $paid = round((float) $sale->paid_amount + $amount, 2);
                $remaining = round((float) $sale->total - $paid - (float) $sale->refunded_amount, 2);

                $sale->update([
                    'paid_amount' => $paid,
                    'remaining_balance' => max(0, $remaining),
                    'is_debt' => $remaining > 0,
                    'status' => $remaining <= 0 ? 'completed' : $sale->status,
                    'completed_at' => $remaining <= 0 ? now() : $sale->completed_at,
                ]);

                if ($remaining <= 0) {
                    SaleTableRelease::releaseIfSettled($sale->fresh());
                }
            });

            $this->reset(['paymentSaleId', 'payment_reference']);
            $this->payment_method = 'cash';
            $this->payment_amount = 0;
            $this->dispatch('close-modal', 'debtor-payment-modal');

            LivewireAlert::title('Payment Recorded')
                ->text('The debtor balance has been updated.')
                ->success()
                ->show();
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('DebtorsIndex::collectPayment failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'paymentSaleId' => $this->paymentSaleId ?? null]);
            LivewireAlert::title('Error')
                ->text('Unable to record payment. Please try again or contact support.')
                ->error()
                ->show();
        }
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
