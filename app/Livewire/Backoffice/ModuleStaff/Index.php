<?php

namespace App\Livewire\Backoffice\ModuleStaff;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\BranchStaff;
use App\Models\Module;
use App\Models\ModuleStaff;
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

    public $module_id;

    public $is_active = true;

    public $search = '';

    public $filterModule = '';
    public $filterBranch = '';

    public $selected = [];

    protected function rules()
    {
        return [

            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',

            'module_id' => 'required|exists:modules,id',

            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.module-staff.index', [

            'assignments' => ModuleStaff::with([
                'user',
                'branch',
                'module',
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
                ->when(
                    $this->filterModule,
                    fn($query) =>
                    $query->where('module_id', $this->filterModule)
                )
                ->latest()
                ->paginate(10),

            'formUsers' => $this->eligibleUsersForSelectedBranch(),

            'branches' => $this->accessibleBranches(),

            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),

            'formModules' => $this->branch_id
                ? $this->accessibleModules((int) $this->branch_id)
                : collect(),

        ]);
    }

    public function updatedBranchId(): void
    {
        $this->user_id = null;
        $this->module_id = null;
    }

    public function resetForm()
    {
        $this->reset([
            'assignmentId',
            'user_id',
            'branch_id',
            'module_id',
        ]);

        $this->is_active = true;
    }

    public function create()
    {
        $this->resetForm();

        $this->dispatch('open-modal', 'module-staff-form');
    }

    public function edit($id)
    {
        $assignment = ModuleStaff::findOrFail($id);
        $this->authorizeBranch($assignment->branch_id);

        $this->assignmentId = $assignment->id;

        $this->user_id = $assignment->user_id;
        $this->branch_id = $assignment->branch_id;

        $this->module_id = $assignment->module_id;

        $this->is_active = $assignment->is_active;

        $this->dispatch('open-modal', 'module-staff-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        $module = Module::findOrFail($this->module_id);
        $selectedUser = User::with('roles')->findOrFail($this->user_id);

        if ($selectedUser->isSuperAdmin()) {
            $this->addError('user_id', 'Super Admin users do not require module assignments.');
            return;
        }

        if (! auth()->user()?->isSuperAdmin() && $selectedUser->isBranchManager()) {
            $this->addError('user_id', 'Only Super Admin users can assign modules to Branch Manager users.');
            return;
        }

        if ((int) $module->branch_id !== (int) $this->branch_id) {
            $this->addError('module_id', 'The selected module does not belong to the selected branch.');
            return;
        }

        $branchAssignment = BranchStaff::query()
            ->where('user_id', $this->user_id)
            ->where('branch_id', $this->branch_id)
            ->first();

        $hasBranchAssignment = $branchAssignment?->is_active;

        $hasAnyBranchAssignment = BranchStaff::query()
            ->where('user_id', $this->user_id)
            ->where('is_active', true)
            ->exists();

        if (! $hasBranchAssignment && $hasAnyBranchAssignment) {
            $this->addError('user_id', 'Assign this user to the selected branch before assigning modules.');
            return;
        }

        if (! $hasBranchAssignment) {
            BranchStaff::updateOrCreate(
                [
                    'user_id' => $this->user_id,
                    'branch_id' => $this->branch_id,
                ],
                [
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'is_active' => true,
                ]
            );
        }

        ModuleStaff::updateOrCreate(
            $this->assignmentId
                ? ['id' => $this->assignmentId]
                : ['user_id' => $this->user_id, 'module_id' => $this->module_id],
            [
                'user_id' => $this->user_id,
                'branch_id' => $this->branch_id,

                'module_id' => $this->module_id,

                'assigned_by' => auth()->id(),

                'is_active' => $this->is_active,
            ]
        );

        $this->dispatch('close-modal', 'module-staff-form');

        LivewireAlert::title('Assignment Saved')
            ->text('Module staff assignment saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id): void
    {
        LivewireAlert::title('Delete Assignment')
            ->text('Are you sure you want to remove this module assignment?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data): void
    {
        $id = $data['id'];
        $assignment = ModuleStaff::findOrFail($id);
        $this->authorizeBranch($assignment->branch_id);
        $assignment->delete();

        LivewireAlert::title('Deleted')
            ->text('Assignment removed successfully.')
            ->success()
            ->show();
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {

            LivewireAlert::title('No Selection')
                ->text('Please select assignments.')
                ->warning()
                ->show();

            return;
        }

        LivewireAlert::title('Bulk Delete')
            ->text('Are you sure you want to delete ' . count($this->selected) . ' module assignments?')
            ->asConfirm()
            ->onConfirm('bulkDelete')
            ->show();
    }

    public function bulkDelete(): void
    {
        ModuleStaff::whereIn('id', $this->selected)
            ->whereIn('branch_id', $this->accessibleBranches()->pluck('id'))
            ->delete();

        $count = count($this->selected);

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
                $query->whereHas('branchAssignments', fn ($branchQuery) => $branchQuery
                    ->where('branch_id', $this->branch_id)
                    ->where('is_active', true))
                    ->orWhereDoesntHave('branchAssignments', fn ($branchQuery) => $branchQuery
                        ->where('is_active', true));
            })
            ->orderBy('name')
            ->get();
    }
}
