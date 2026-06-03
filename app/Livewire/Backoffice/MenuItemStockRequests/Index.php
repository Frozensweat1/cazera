<?php

namespace App\Livewire\Backoffice\MenuItemStockRequests;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\MenuItem;
use App\Models\MenuItemAdjustment;
use App\Models\MenuItemStockRequest;
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
    public $menu_item_id;
    public $requested_qty = 1;
    public string $reason = '';
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'module_id' => ['required', 'exists:modules,id'],
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'requested_qty' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.menu-item-stock-requests.index', [
            'requests' => MenuItemStockRequest::with(['branch', 'module', 'menuItem', 'requester', 'approver'])
                ->accessible()
                ->when($this->search, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                    ->where('reference_no', 'like', "%{$this->search}%")
                    ->orWhere('reason', 'like', "%{$this->search}%")
                    ->orWhereHas('menuItem', fn (Builder $itemQuery) => $itemQuery->where('name', 'like', "%{$this->search}%"))))
                ->when($this->filterBranch, fn (Builder $query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn (Builder $query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterStatus, fn (Builder $query) => $query->where('status', $this->filterStatus))
                ->latest('requested_at')
                ->paginate(12),
            'branches' => $this->accessibleBranches(),
            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),
            'formModules' => $this->branch_id ? $this->accessibleModules((int) $this->branch_id) : collect(),
            'menuItems' => MenuItem::accessible()
                ->where('is_trackable', true)
                ->where('status', '!=', 'unavailable')
                ->when($this->branch_id, fn (Builder $query) => $query->where('branch_id', $this->branch_id))
                ->when($this->module_id, fn (Builder $query) => $query->where('module_id', $this->module_id))
                ->orderBy('name')
                ->get(),
            'canApprove' => $this->canApproveRequests(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'menu-item-stock-request-form');
    }

    public function submit(): void
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        $item = MenuItem::accessible()->findOrFail($this->menu_item_id);

        abort_unless($item->is_trackable, 422, 'Only trackable menu items can receive stock requests.');
        abort_unless((int) $item->branch_id === (int) $this->branch_id && (int) $item->module_id === (int) $this->module_id, 403);

        MenuItemStockRequest::create([
            'branch_id' => $item->branch_id,
            'module_id' => $item->module_id,
            'menu_item_id' => $item->id,
            'requested_by' => auth()->id(),
            'reference_no' => $this->generateReference(),
            'requested_qty' => $this->requested_qty,
            'status' => 'pending',
            'reason' => $this->reason ?: null,
            'notes' => $this->notes ?: null,
            'requested_at' => now(),
        ]);

        $this->dispatch('close-modal', 'menu-item-stock-request-form');
        $this->resetForm();

        LivewireAlert::title('Stock Request Submitted')
            ->text('The request is pending approval.')
            ->success()
            ->show();
    }

    public function approve(int $id): void
    {
        abort_unless($this->canApproveRequests(), 403);

        DB::transaction(function () use ($id) {
            $request = MenuItemStockRequest::accessible()->lockForUpdate()->findOrFail($id);
            abort_unless($request->isPending(), 422, 'Only pending requests can be approved.');

            $item = MenuItem::query()->lockForUpdate()->findOrFail($request->menu_item_id);
            abort_unless($item->is_trackable, 422, 'Only trackable menu items can receive stock.');
            $this->authorizeBranch($request->branch_id);
            $this->authorizeModule($request->module_id, $request->branch_id);

            $before = (float) ($item->quantity ?? 0);
            $after = $before + (float) $request->requested_qty;

            MenuItemAdjustment::create([
                'branch_id' => $request->branch_id,
                'module_id' => $request->module_id,
                'menu_item_id' => $request->menu_item_id,
                'sale_id' => null,
                'performed_by' => auth()->id(),
                'type' => 'production',
                'quantity_before' => $before,
                'quantity_after' => $after,
                'change_qty' => $request->requested_qty,
                'reference_no' => $request->reference_no,
                'reason' => 'Approved menu stock request',
                'notes' => $request->notes,
                'transaction_date' => now(),
            ]);

            $item->update([
                'quantity' => $after,
                'status' => $after > 0 && $item->status === 'out_of_stock' ? 'available' : $item->status,
            ]);

            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'quantity_before' => $before,
                'quantity_after' => $after,
            ]);
        });

        LivewireAlert::title('Request Approved')
            ->text('Menu item quantity has been increased and adjustment history was recorded.')
            ->success()
            ->show();
    }

    public function reject(int $id): void
    {
        abort_unless($this->canApproveRequests(), 403);

        $request = MenuItemStockRequest::accessible()->findOrFail($id);
        abort_unless($request->isPending(), 422, 'Only pending requests can be rejected.');

        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => 'Rejected by ' . auth()->user()?->name,
        ]);

        LivewireAlert::title('Request Rejected')->success()->show();
    }

    public function cancel(int $id): void
    {
        $request = MenuItemStockRequest::accessible()->findOrFail($id);
        abort_unless($request->isPending() && (int) $request->requested_by === (int) auth()->id(), 403);

        $request->update(['status' => 'cancelled']);
        LivewireAlert::title('Request Cancelled')->success()->show();
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

    public function updatedBranchId(): void
    {
        $this->module_id = null;
        $this->menu_item_id = null;
    }

    public function updatedModuleId(): void
    {
        $this->menu_item_id = null;
    }

    protected function resetForm(): void
    {
        $this->reset(['branch_id', 'module_id', 'menu_item_id', 'reason', 'notes']);
        $this->branch_id = session('branch_id') ?: '';
        $this->requested_qty = 1;
    }

    protected function canApproveRequests(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager', 'Inventory Manager']) ?? false;
    }

    protected function generateReference(): string
    {
        return 'MSR-' . now()->format('YmdHis') . '-' . strtoupper(str()->random(4));
    }
}
