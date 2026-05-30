<?php

namespace App\Livewire\Backoffice\BranchStaff;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\BranchStaff;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $assignmentId;
    public $user_id;
    public $branch_id;
    public $is_active = true;
    public $search = '';
    public $filterBranch = '';
    public $selected = [];

    protected function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.branch-staff.index', [
            'assignments' => BranchStaff::with([
                'user',
                'branch',
                'assignedBy',
            ])
                ->whereIn('branch_id', $this->accessibleBranches()->pluck('id'))
                ->when(
                    $this->search,
                    fn($query) =>
                    $query->whereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    })
                )
                ->when(
                    $this->filterBranch,
                    fn($query) =>
                    $query->where('branch_id', $this->filterBranch)
                )
                ->latest()
                ->paginate(10),

            'formUsers' => $this->eligibleUsersForSelectedBranch(),
            'branches' => $this->accessibleBranches(),
        ]);
    }

    public function updatedBranchId(): void
    {
        $this->user_id = null;
    }

    public function resetForm()
    {
        $this->reset([
            'assignmentId',
            'user_id',
            'branch_id',
        ]);
        $this->is_active = true;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'branch-staff-form');
    }

    public function edit($id)
    {
        $assignment = BranchStaff::findOrFail($id);
        $this->authorizeBranch($assignment->branch_id);
        $this->assignmentId = $assignment->id;
        $this->user_id = $assignment->user_id;
        $this->branch_id = $assignment->branch_id;
        $this->is_active = $assignment->is_active;

        $this->dispatch('open-modal', 'branch-staff-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);

        $selectedUser = User::with('roles')->findOrFail($this->user_id);

        if ($selectedUser->isSuperAdmin()) {
            $this->addError('user_id', 'Super Admin users do not require branch assignments.');
            return;
        }

        if (! auth()->user()?->isSuperAdmin() && $selectedUser->isBranchManager()) {
            $this->addError('user_id', 'Only Super Admin users can assign Branch Manager users to branches.');
            return;
        }

        BranchStaff::updateOrCreate(
            $this->assignmentId
                ? ['id' => $this->assignmentId]
                : ['user_id' => $this->user_id, 'branch_id' => $this->branch_id],
            [
                'user_id' => $this->user_id,
                'branch_id' => $this->branch_id,
                'assigned_by' => auth()->id(),
                'is_active' => $this->is_active,
                'assigned_at' => now(),
            ]
        );

        $this->dispatch('close-modal', 'branch-staff-form');

        LivewireAlert::title('Assignment Saved')
            ->text('Branch staff assignment saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        LivewireAlert::title('Delete Assignment')
            ->text('Are you sure you want to remove this staff assignment?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data)
    {
        $id = $data['id'];
        $assignment = BranchStaff::findOrFail($id);
        $this->authorizeBranch($assignment->branch_id);
        $assignment->delete();

        LivewireAlert::title('Deleted')
            ->text('Branch assignment removed successfully.')
            ->success()
            ->show();
    }

    public function confirmBulkDelete()
    {
        if (empty($this->selected)) {
            LivewireAlert::title('No Selection')
                ->text('Please select assignments.')
                ->warning()
                ->show();
            return;
        }
        LivewireAlert::title('Bulk Delete')
            ->text('Are you sure you want to delete ' . count($this->selected) . ' assignments?')
            ->asConfirm()
            ->onConfirm('bulkDelete')
            ->show();
    }

    public function bulkDelete()
    {
        $count = count($this->selected);
        BranchStaff::whereIn('id', $this->selected)
            ->whereIn('branch_id', $this->accessibleBranches()->pluck('id'))
            ->delete();
        $this->selected = [];

        LivewireAlert::title('Bulk Delete Complete')
            ->text($count . ' assignments deleted.')
            ->success()
            ->show();
    }

    protected function eligibleUsersForSelectedBranch()
    {
        if (! $this->branch_id) {
            return collect();
        }

        $this->authorizeBranch($this->branch_id);

        return User::query()
            ->with('roles')
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'Super Admin'))
            ->when(! auth()->user()?->isSuperAdmin(), fn ($query) => $query
                ->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->where('name', 'Branch Manager')))
            ->where(function ($query) {
                $query->whereDoesntHave('branchAssignments', fn ($branchQuery) => $branchQuery
                    ->where('branch_id', $this->branch_id)
                    ->where('is_active', true));

                if ($this->assignmentId && $this->user_id) {
                    $query->orWhere('id', $this->user_id);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
