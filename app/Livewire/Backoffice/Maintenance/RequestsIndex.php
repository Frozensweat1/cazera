<?php

namespace App\Livewire\Backoffice\Maintenance;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\MaintenanceRequest;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class RequestsIndex extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterStatus = '';
    public $filterPriority = '';

    public $editingId;
    public $branch_id = '';
    public $module_id = '';
    public $equipment_name = '';
    public $description = '';
    public $type = 'corrective';
    public $priority = 'medium';
    public $estimated_cost = 0;
    public $actual_cost;
    public $scheduled_date;
    public $notes = '';

    public $rejectingId;
    public $rejection_reason = '';
    public $completingId;
    public $completion_actual_cost;

    public function render()
    {
        $branchId = $this->filterBranch ?: (auth()->user()?->isSuperAdmin() ? null : session('branch_id'));

        return view('livewire.backoffice.maintenance.requests-index', [
            'requests' => MaintenanceRequest::with(['branch', 'module', 'requestedBy', 'approvedBy', 'executedBy', 'locker'])
                ->accessible()
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
                ->when($this->filterPriority, fn ($query) => $query->where('priority', $this->filterPriority))
                ->when($this->search, fn ($query) => $query->where(function ($query) {
                    $query->where('equipment_name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                }))
                ->latest('requested_date')
                ->paginate(15),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($branchId ?: $this->branch_id ?: null),
            'formModules' => $this->accessibleModules($this->branch_id ?: $branchId ?: null),
            'canManageRestrictedActions' => $this->canManageRestrictedActions(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'maintenance-request-form');
    }

    public function edit($id): void
    {
        $request = MaintenanceRequest::accessible()->findOrFail($id);

        if ($request->is_locked) {
            $this->lockedAlert();
            return;
        }

        $this->editingId = $request->id;
        $this->branch_id = $request->branch_id;
        $this->module_id = $request->module_id;
        $this->equipment_name = $request->equipment_name;
        $this->description = $request->description;
        $this->type = $request->type;
        $this->priority = $request->priority;
        $this->estimated_cost = $request->estimated_cost;
        $this->actual_cost = $request->actual_cost;
        $this->scheduled_date = $request->scheduled_date?->format('Y-m-d');
        $this->notes = $request->notes ?? '';

        $this->dispatch('open-modal', 'maintenance-request-form');
    }

    public function save(): void
    {
        $this->validate([
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'equipment_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'type' => 'required|in:preventive,corrective,inspection,replacement,repair',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_cost' => 'required|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'scheduled_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->authorizeBranch($this->branch_id);

        if ($this->module_id) {
            $this->authorizeModule($this->module_id, $this->branch_id);
        }

        if ($this->editingId) {
            $request = MaintenanceRequest::accessible()->findOrFail($this->editingId);

            if ($request->is_locked) {
                $this->lockedAlert();
                return;
            }

            $request->update([
                'branch_id' => $this->branch_id,
                'module_id' => $this->module_id ?: null,
                'equipment_name' => $this->equipment_name,
                'description' => $this->description,
                'type' => $this->type,
                'priority' => $this->priority,
                'estimated_cost' => $this->estimated_cost,
                'actual_cost' => $this->actual_cost,
                'scheduled_date' => $this->scheduled_date,
                'notes' => $this->notes,
            ]);

            $message = 'Maintenance request updated successfully.';
        } else {
            MaintenanceRequest::create([
                'branch_id' => $this->branch_id,
                'module_id' => $this->module_id ?: null,
                'equipment_name' => $this->equipment_name,
                'description' => $this->description,
                'type' => $this->type,
                'priority' => $this->priority,
                'status' => 'requested',
                'requested_by' => auth()->id(),
                'estimated_cost' => $this->estimated_cost,
                'actual_cost' => $this->actual_cost,
                'scheduled_date' => $this->scheduled_date,
                'notes' => $this->notes,
            ]);

            $message = 'New maintenance request submitted successfully.';
        }

        $this->dispatch('close-modal', 'maintenance-request-form');
        $this->resetForm();

        LivewireAlert::title('Maintenance Request Saved')
            ->text($message)
            ->success()
            ->show();
    }

    public function approve(int $id): void
    {
        $request = $this->editableRequest($id);

        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_date' => now(),
        ]);

        LivewireAlert::title('Request Approved')->success()->show();
    }

    public function openReject(int $id): void
    {
        $this->editableRequest($id);
        $this->rejectingId = $id;
        $this->rejection_reason = '';
        $this->dispatch('open-modal', 'maintenance-reject-modal');
    }

    public function reject(): void
    {
        $this->validate(['rejection_reason' => 'required|string|max:1000']);

        $request = $this->editableRequest($this->rejectingId);
        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejection_reason,
        ]);

        $this->dispatch('close-modal', 'maintenance-reject-modal');
        $this->reset(['rejectingId', 'rejection_reason']);

        LivewireAlert::title('Request Rejected')->warning()->show();
    }

    public function markInProgress(int $id): void
    {
        $request = $this->editableRequest($id);

        $request->update([
            'status' => 'in_progress',
            'executed_by' => auth()->id(),
        ]);

        LivewireAlert::title('Work Started')->success()->show();
    }

    public function openComplete(int $id): void
    {
        abort_unless($this->canManageRestrictedActions(), 403);

        $request = $this->editableRequest($id);
        $this->completingId = $id;
        $this->completion_actual_cost = $request->actual_cost ?: $request->estimated_cost;
        $this->dispatch('open-modal', 'maintenance-complete-modal');
    }

    public function markCompleted(): void
    {
        abort_unless($this->canManageRestrictedActions(), 403);

        $this->validate(['completion_actual_cost' => 'nullable|numeric|min:0']);

        $request = $this->editableRequest($this->completingId);
        $request->update([
            'status' => 'completed',
            'completed_date' => now(),
            'actual_cost' => $this->completion_actual_cost,
        ]);

        $this->dispatch('close-modal', 'maintenance-complete-modal');
        $this->reset(['completingId', 'completion_actual_cost']);

        LivewireAlert::title('Work Completed')->success()->show();
    }

    public function cancel(int $id): void
    {
        $request = $this->editableRequest($id);
        $request->update(['status' => 'cancelled']);

        LivewireAlert::title('Request Cancelled')->warning()->show();
    }

    public function lockRequest(int $id): void
    {
        abort_unless($this->canManageRestrictedActions(), 403);

        MaintenanceRequest::accessible()->findOrFail($id)->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ]);

        LivewireAlert::title('Request Locked')->success()->show();
    }

    public function unlockRequest(int $id): void
    {
        abort_unless($this->canManageRestrictedActions(), 403);

        MaintenanceRequest::accessible()->findOrFail($id)->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        LivewireAlert::title('Request Unlocked')->success()->show();
    }

    public function confirmDelete(int $id): void
    {
        $request = MaintenanceRequest::accessible()->findOrFail($id);

        if ($request->is_locked) {
            $this->lockedAlert('Locked maintenance requests cannot be deleted.');
            return;
        }

        LivewireAlert::title('Delete Maintenance Request')
            ->text('Are you sure you want to delete this maintenance request?')
            ->asConfirm()
            ->onConfirm('delete', ['id' => $id])
            ->show();
    }

    public function delete(array $data): void
    {
        $id = $data['id'];
        $request = MaintenanceRequest::accessible()->findOrFail($id);

        if ($request->is_locked) {
            $this->lockedAlert('Locked maintenance requests cannot be deleted.');
            return;
        }

        $request->delete();

        LivewireAlert::title('Request Deleted')->success()->show();
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
        $this->resetPage();
    }

    public function updatedBranchId(): void
    {
        $this->module_id = '';
    }

    protected function editableRequest($id): MaintenanceRequest
    {
        $request = MaintenanceRequest::accessible()->findOrFail($id);

        if ($request->is_locked) {
            $this->lockedAlert();
            abort(423, 'This maintenance request is locked.');
        }

        return $request;
    }

    protected function canManageRestrictedActions(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    protected function lockedAlert(string $message = 'Locked maintenance requests cannot be modified.'): void
    {
        LivewireAlert::title('Request Locked')
            ->text($message)
            ->warning()
            ->show();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId',
            'module_id',
            'equipment_name',
            'description',
            'type',
            'priority',
            'estimated_cost',
            'actual_cost',
            'scheduled_date',
            'notes',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->type = 'corrective';
        $this->priority = 'medium';
        $this->estimated_cost = 0;
    }
}
