<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Customer;
use App\Models\MenuItemAdjustment;
use App\Models\Sale;
use App\Support\PosCache;
use App\Support\SalePaymentRecorder;
use App\Support\SaleRefundSettlement;
use App\Support\SaleTableRelease;
use App\Support\WebsiteContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class SalesIndex extends Component
{
    use HasBranchScope;
    use WithPagination;

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
            'sales' => Sale::with(['branch', 'customer', 'modules', 'creator', 'payments', 'table'])
                ->accessible()
                ->where('status', '!=', 'refunded')
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->forModule($this->filterModule)
                ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
                ->when($this->search, fn ($query) => $query->where(function ($query) {
                    $query->where('sale_number', 'like', "%{$this->search}%")
                        ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', "%{$this->search}%"));
                }))
                ->latest('sale_date')
                ->paginate(15),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: null),
            'receiptSale' => $this->receiptSaleId
                ? Sale::with(['branch', 'modules', 'customer', 'creator', 'items', 'payments.receiver'])
                    ->accessible()
                    ->find($this->receiptSaleId)
                : null,
            'paymentSale' => $this->paymentSaleId
                ? Sale::with(['customer', 'modules'])->accessible()->find($this->paymentSaleId)
                : null,
            'refundSale' => $this->refundSaleId
                ? Sale::with(['customer', 'modules'])->accessible()->find($this->refundSaleId)
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
        try {
            $this->validate([
                'paymentSaleId' => 'required|exists:sales,id',
                'payment_method' => ['required', Rule::in(['cash', 'mobile_money', 'card', 'bank_transfer', 'wallet'])],
                'payment_amount' => 'required|numeric|min:0.01',
                'payment_reference' => 'nullable|string|max:255',
            ]);

            DB::transaction(function () {
                $sale = Sale::accessible()->with('items')->lockForUpdate()->findOrFail($this->paymentSaleId);
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
                    reference: $this->payment_reference,
                    userId: (int) auth()->id(),
                    registerName: 'Auto-opened POS register',
                    notes: 'Balance payment for sale '.$sale->sale_number,
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

            $this->dispatch('close-modal', 'sales-payment-modal');

            LivewireAlert::title('Payment Recorded')
                ->text('Outstanding balance has been updated.')
                ->success()
                ->show();
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('SalesIndex::collectPayment failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'paymentSaleId' => $this->paymentSaleId ?? null]);
            LivewireAlert::title('Error')
                ->text('Unable to record payment. Please try again or contact support.')
                ->error()
                ->show();
        }
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
        try {
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

                if ($amount > (float) $sale->paid_amount) {
                    throw ValidationException::withMessages([
                        'refund_amount' => 'Refund cannot exceed the paid amount.',
                    ]);
                }

                $settlement = SaleRefundSettlement::calculate(
                    total: (float) $sale->total,
                    paid: (float) $sale->paid_amount,
                    refundAmount: $amount,
                    currentStatus: $sale->status,
                    alreadyRefunded: (float) $sale->refunded_amount,
                );

                SalePaymentRecorder::recordRefund(
                    sale: $sale,
                    method: $this->refund_method,
                    amount: $amount,
                    userId: (int) auth()->id(),
                    registerName: 'Auto-opened refund register',
                    notes: $this->refund_reason ?: 'Refund for sale '.$sale->sale_number,
                );

                if ($settlement['is_full_refund']) {
                    foreach ($sale->items as $item) {
                        if (! $item->is_trackable) {
                            continue;
                        }

                        $menuItem = $item->menuItem()->lockForUpdate()->first();

                        if (! $menuItem) {
                            continue;
                        }

                        $before = (float) $menuItem->quantity;
                        $after = $before + (float) $item->qty;

                        $menuItem->update([
                            'quantity' => $after,
                            'status' => $menuItem->status === 'out_of_stock' && $after > 0 ? 'available' : $menuItem->status,
                        ]);

                        MenuItemAdjustment::create([
                            'branch_id' => $sale->branch_id,
                            'module_id' => $item->module_id,
                            'menu_item_id' => $item->menu_item_id,
                            'sale_id' => $sale->id,
                            'performed_by' => auth()->id(),
                            'type' => 'refund',
                            'quantity_before' => $before,
                            'quantity_after' => $after,
                            'change_qty' => (float) $item->qty,
                            'reference_no' => $sale->sale_number,
                            'reason' => 'Full sale refund return',
                            'transaction_date' => now(),
                        ]);
                    }

                    $sale->items()->update([
                        'status' => 'cancelled',
                        'kitchen_status' => 'completed',
                        'kitchen_completed_at' => now(),
                    ]);
                    SaleTableRelease::release($sale);
                }

                $sale->update([
                    'total' => $settlement['total'],
                    'paid_amount' => $settlement['paid_amount'],
                    'refunded_amount' => $settlement['refunded_amount'],
                    'remaining_balance' => $settlement['remaining_balance'],
                    'is_debt' => $settlement['is_debt'],
                    'status' => $settlement['status'],
                ]);

                if ($sale->customer) {
                    Customer::whereKey($sale->customer_id)->update([
                        'total_spent' => DB::raw('GREATEST(COALESCE(total_spent, 0) - '.$settlement['customer_spend_reduction'].', 0)'),
                    ]);
                }
            });

            $this->dispatch('close-modal', 'sales-refund-modal');

            LivewireAlert::title('Refund Processed')
                ->text('Refund has been recorded successfully.')
                ->success()
                ->show();
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('SalesIndex::processRefund failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'refundSaleId' => $this->refundSaleId ?? null]);
            LivewireAlert::title('Error')
                ->text('Unable to process refund. Please try again or contact support.')
                ->error()
                ->show();
        }
    }

    public function releaseTable(int $saleId): void
    {
        try {
            DB::transaction(function () use ($saleId) {
                $sale = Sale::accessible()
                    ->with('table')
                    ->whereNotNull('table_id')
                    ->lockForUpdate()
                    ->findOrFail($saleId);

                abort_unless(SaleTableRelease::canRelease($sale), 422, 'Only completed or fully paid table orders can be released.');

                SaleTableRelease::release($sale);
            });

            LivewireAlert::title('Table Released')
                ->text('The dining table is now available.')
                ->success()
                ->show();
        } catch (Throwable $e) {
            Log::error('SalesIndex::releaseTable failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'sale_id' => $saleId]);
            LivewireAlert::title('Unable to Release Table')
                ->text('Please refresh and try again.')
                ->error()
                ->show();
        }
    }

    protected function receiptSettings(): array
    {
        return Cache::remember(PosCache::receiptSettingsKey(), now()->addMinutes(5), function () {
            $settings = WebsiteContent::settings();

            return [
                'business_name' => $settings?->business_name ?: config('app.name', 'Cazera'),
                'tagline' => $settings?->tagline,
                'address' => $settings?->address,
                'phone' => $settings?->phone,
                'email' => $settings?->email,
                'whatsapp' => $settings?->whatsapp,
            ];
        });
    }
}
