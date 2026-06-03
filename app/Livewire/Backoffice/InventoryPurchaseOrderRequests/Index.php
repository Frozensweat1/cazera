<?php

namespace App\Livewire\Backoffice\InventoryPurchaseOrderRequests;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryItem;
use App\Models\InventoryItemStock;
use App\Models\InventoryPurchaseOrderRequest;
use App\Models\InventoryStockAdjustment;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use HasBranchScope;
    use WithPagination;

    public string $search = '';
    public string $filterBranch = '';
    public string $filterModule = '';
    public string $filterStatus = '';

    public $branch_id;
    public $module_id;
    public $inventory_item_id;
    public $supplier_id;
    public $requested_qty = 1;
    public $unit_cost = 0;
    public string $expected_delivery_date = '';
    public string $reason = '';
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'module_id' => ['nullable', 'exists:modules,id'],
            'inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'requested_qty' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'expected_delivery_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.inventory-purchase-order-requests.index', [
            'requests' => $this->purchaseRequestsQuery()
                ->when($this->search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                    ->where('reference_no', 'like', "%{$this->search}%")
                    ->orWhere('reason', 'like', "%{$this->search}%")
                    ->orWhereHas('inventoryItem', fn (Builder $itemQuery) => $itemQuery->where('name', 'like', "%{$this->search}%"))))
                ->when($this->filterBranch, fn (Builder $query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn (Builder $query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterStatus, fn (Builder $query) => $query->where('status', $this->filterStatus))
                ->latest('requested_at')
                ->paginate(12),
            'branches' => $this->accessibleBranches(),
            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),
            'formModules' => $this->branch_id ? $this->accessibleModules((int) $this->branch_id) : collect(),
            'items' => InventoryItem::accessible()
                ->where('is_trackable', true)
                ->where('is_active', true)
                ->when($this->branch_id, fn (Builder $query) => $query->where('branch_id', $this->branch_id))
                ->when($this->module_id, fn (Builder $query) => $query->where('module_id', $this->module_id))
                ->orderBy('name')
                ->get(),
            'suppliers' => Supplier::accessible()
                ->where('is_active', true)
                ->when($this->branch_id, fn (Builder $query) => $query->where('branch_id', $this->branch_id))
                ->when($this->module_id, fn (Builder $query) => $query->where(fn (Builder $query) => $query->whereNull('module_id')->orWhere('module_id', $this->module_id)))
                ->orderBy('name')
                ->get(),
            'canSubmit' => $this->canSubmitRequests(),
            'canApprove' => $this->canApproveRequests(),
        ]);
    }

    public function create(): void
    {
        abort_unless($this->canSubmitRequests(), 403);
        $this->resetForm();
        $this->dispatch('open-modal', 'inventory-purchase-order-request-form');
    }

    public function submit(): void
    {
        abort_unless($this->canSubmitRequests(), 403);
        $this->validate();
        $this->authorizeBranch($this->branch_id);

        if ($this->module_id) {
            $this->authorizeModule($this->module_id, $this->branch_id);
        }

        $item = InventoryItem::accessible()->findOrFail($this->inventory_item_id);
        abort_unless($item->is_trackable, 422, 'Only trackable inventory items can be purchased into stock.');
        abort_unless((int) $item->branch_id === (int) $this->branch_id, 403);

        if ($this->module_id) {
            abort_unless((int) $item->module_id === (int) $this->module_id, 403);
        }

        InventoryPurchaseOrderRequest::create([
            'branch_id' => $item->branch_id,
            'module_id' => $item->module_id,
            'inventory_item_id' => $item->id,
            'supplier_id' => $this->supplier_id ?: $item->supplier_id,
            'requested_by' => auth()->id(),
            'reference_no' => $this->generateReference(),
            'requested_qty' => $this->requested_qty,
            'unit_cost' => $this->unit_cost,
            'total_cost' => round((float) $this->requested_qty * (float) $this->unit_cost, 2),
            'status' => 'pending',
            'reason' => $this->reason ?: null,
            'notes' => $this->notes ?: null,
            'expected_delivery_date' => $this->expected_delivery_date ?: null,
            'requested_at' => now(),
        ]);

        $this->dispatch('close-modal', 'inventory-purchase-order-request-form');
        $this->resetForm();

        LivewireAlert::title('Purchase Request Submitted')
            ->text('The request is pending approval.')
            ->success()
            ->show();
    }

    public function approve(int $id): void
    {
        abort_unless($this->canApproveRequests(), 403);

        DB::transaction(function () use ($id) {
            $request = $this->purchaseRequestsQuery()->lockForUpdate()->findOrFail($id);
            abort_unless($request->isPending(), 422, 'Only pending purchase requests can be approved.');

            $item = InventoryItem::query()->lockForUpdate()->findOrFail($request->inventory_item_id);
            abort_unless($item->is_trackable, 422, 'Only trackable inventory items can receive stock.');
            $this->authorizeBranch($request->branch_id);

            if ($request->module_id) {
                $this->authorizeModule($request->module_id, $request->branch_id);
            }

            $stock = InventoryItemStock::receivingBalanceForItem($item->id);
            $before = (float) $stock->quantity_on_hand;
            $after = $before + (float) $request->requested_qty;

            InventoryStockAdjustment::create([
                'branch_id' => $item->branch_id,
                'module_id' => $item->module_id,
                'inventory_item_id' => $item->id,
                'performed_by' => auth()->id(),
                'type' => 'purchase',
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_qty' => $request->requested_qty,
                'reference_no' => $request->reference_no,
                'reason' => 'Approved purchase order request',
                'notes' => $request->notes,
                'transaction_date' => now(),
            ]);

            $stock->update(['quantity_on_hand' => $after]);

            $item->fill([
                'unit_cost' => $request->unit_cost,
                'supplier_id' => $request->supplier_id ?: $item->supplier_id,
            ])->save();
            $item->refreshAggregateQuantity();

            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'quantity_before' => $before,
                'quantity_after' => $after,
            ]);
        });

        LivewireAlert::title('Purchase Approved')
            ->text('Inventory stock has been increased and adjustment history was recorded.')
            ->success()
            ->show();
    }

    public function reject(int $id): void
    {
        abort_unless($this->canApproveRequests(), 403);

        $request = $this->purchaseRequestsQuery()->findOrFail($id);
        abort_unless($request->isPending(), 422, 'Only pending purchase requests can be rejected.');

        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => 'Rejected by ' . auth()->user()?->name,
        ]);

        LivewireAlert::title('Purchase Rejected')->success()->show();
    }

    public function cancel(int $id): void
    {
        $request = $this->purchaseRequestsQuery()->findOrFail($id);
        abort_unless($request->isPending() && (int) $request->requested_by === (int) auth()->id(), 403);

        $request->update(['status' => 'cancelled']);
        LivewireAlert::title('Purchase Request Cancelled')->success()->show();
    }

    public function updatedInventoryItemId(): void
    {
        if (! $this->inventory_item_id) {
            return;
        }

        $item = InventoryItem::accessible()->findOrFail($this->inventory_item_id);
        $this->branch_id = $item->branch_id;
        $this->module_id = $item->module_id;
        $this->supplier_id = $item->supplier_id;
        $this->unit_cost = $item->unit_cost ?? 0;
    }

    public function updatedBranchId(): void
    {
        $this->module_id = null;
        $this->inventory_item_id = null;
        $this->supplier_id = null;
    }

    public function updatedModuleId(): void
    {
        $this->inventory_item_id = null;
        $this->supplier_id = null;
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

    protected function resetForm(): void
    {
        $this->reset(['branch_id', 'module_id', 'inventory_item_id', 'supplier_id', 'expected_delivery_date', 'reason', 'notes']);
        $this->branch_id = session('branch_id') ?: '';
        $this->requested_qty = 1;
        $this->unit_cost = 0;
    }

    protected function canSubmitRequests(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'Inventory Manager']) ?? false;
    }

    protected function purchaseRequestsQuery(): Builder
    {
        $query = InventoryPurchaseOrderRequest::with(['branch', 'module', 'inventoryItem', 'supplier', 'requester', 'approver']);

        if (! auth()->user()?->isSuperAdmin()) {
            $query->whereIn('branch_id', auth()->user()?->accessibleBranches()->pluck('branches.id') ?? []);
        }

        return $query;
    }

    protected function canApproveRequests(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'Accountant']) ?? false;
    }

    protected function generateReference(): string
    {
        return 'POR-' . now()->format('YmdHis') . '-' . strtoupper(str()->random(4));
    }
}
