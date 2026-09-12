<?php

namespace App\Livewire\Backoffice\BranchStaff;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\BranchStaff;
use App\Models\User;
use App\Support\PosCache;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use HasBranchScope;
    use WithPagination;

    public $assignmentId;

    public $user_id;

    public array $branch_ids = [];

    public $is_active = true;

    public $search = '';

    public $filterBranch = '';

    public $selected = [];

    protected function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'branch_ids' => 'required|array|min:1',
            'branch_ids.*' => 'integer|exists:branches,id',
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
                    fn ($query) => $query->whereHas('user', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    })
                )
                ->when(
                    $this->filterBranch,
                    fn ($query) => $query->where('branch_id', $this->filterBranch)
                )
                ->latest()
                ->paginate(10),

            'formUsers' => $this->eligibleUsersForAssignment(),
            'branches' => $this->accessibleBranches(),
        ]);
    }

    public function resetForm()
    {
        $this->reset([
            'assignmentId',
            'user_id',
            'branch_ids',
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
        $this->branch_ids = [(int) $assignment->branch_id];
        $this->is_active = $assignment->is_active;

        $this->dispatch('open-modal', 'branch-staff-form');
    }

    public function save()
    {
        $this->validate();

        $branchIds = collect($this->branch_ids)
            ->filter()
            ->map(fn ($branchId) => (int) $branchId)
            ->unique()
            ->values();

        if ($branchIds->isEmpty()) {
            $this->addError('branch_ids', 'Please select at least one branch.');

            return;
        }

        foreach ($branchIds as $branchId) {
            $this->authorizeBranch($branchId);
        }

        $selectedUser = User::with('roles')->findOrFail($this->user_id);

        if ($selectedUser->isSuperAdmin()) {
            $this->addError('user_id', 'Super Admin users do not require branch assignments.');

            return;
        }

        if (! auth()->user()?->isSuperAdmin() && $selectedUser->isBranchManager()) {
            $this->addError('user_id', 'Only Super Admin users can assign Branch Manager users to branches.');

            return;
        }

        try {
            DB::transaction(function () use ($branchIds) {
                $currentAssignment = $this->assignmentId
                    ? BranchStaff::find($this->assignmentId)
                    : null;

                foreach ($branchIds as $index => $branchId) {
                    $payload = [
                        'user_id' => $this->user_id,
                        'branch_id' => $branchId,
                        'assigned_by' => auth()->id(),
                        'is_active' => $this->is_active,
                        'assigned_at' => now(),
                    ];

                    if ($currentAssignment && $index === 0) {
                        $duplicate = BranchStaff::query()
                            ->where('user_id', $this->user_id)
                            ->where('branch_id', $branchId)
                            ->whereKeyNot($currentAssignment->id)
                            ->first();

                        if ($duplicate) {
                            $duplicate->update($payload);
                            $currentAssignment->delete();
                        } else {
                            $currentAssignment->update($payload);
                        }

                        continue;
                    }

                    BranchStaff::updateOrCreate(
                        [
                            'user_id' => $this->user_id,
                            'branch_id' => $branchId,
                        ],
                        $payload
                    );
                }
            });
        } catch (\Throwable $exception) {
            report($exception);

            LivewireAlert::title('Error')
                ->text('The branch assignments could not be saved. Please try again.')
                ->error()
                ->show();

            return;
        }

        $this->dispatch('close-modal', 'branch-staff-form');

        LivewireAlert::title('Assignment Saved')
            ->text($branchIds->count().' branch assignment'.($branchIds->count() === 1 ? '' : 's').' saved successfully.')
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
            ->text('Are you sure you want to delete '.count($this->selected).' assignments?')
            ->asConfirm()
            ->onConfirm('bulkDelete')
            ->show();
    }

    public function bulkDelete()
    {
        $count = count($this->selected);
        $query = BranchStaff::whereIn('id', $this->selected)
            ->whereIn('branch_id', $this->accessibleBranches()->pluck('id'));
        $branchIds = (clone $query)->pluck('branch_id')->unique();

        $query->delete();
        $branchIds->each(fn ($branchId) => PosCache::invalidateModuleAccess((int) $branchId));
        $this->selected = [];

        LivewireAlert::title('Bulk Delete Complete')
            ->text($count.' assignments deleted.')
            ->success()
            ->show();
    }

    protected function eligibleUsersForAssignment()
    {
        return User::query()
            ->with('roles')
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'Super Admin'))
            ->when(! auth()->user()?->isSuperAdmin(), fn ($query) => $query
                ->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->where('name', 'Branch Manager')))
            ->orderBy('name')
            ->get();
    }
}
