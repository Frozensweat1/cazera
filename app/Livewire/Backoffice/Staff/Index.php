<?php

namespace App\Livewire\Backoffice\Staff;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use HasBranchScope;
    use WithPagination;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterStatus = '';

    public $staffProfileId;
    public $user_id;
    public $branch_id;
    public $module_id;
    public $employee_code;
    public $job_title;
    public $department;
    public $employment_type = 'full_time';
    public $employment_status = 'active';
    public $hire_date;
    public $date_of_birth;
    public $gender;
    public $national_id;
    public $emergency_contact_name;
    public $emergency_contact_phone;
    public $emergency_contact_relationship;
    public $bank_name;
    public $bank_account_name;
    public $bank_account_number;
    public $address;
    public $notes;

    protected function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('staff_profiles', 'user_id')->ignore($this->staffProfileId),
            ],
            'branch_id' => 'nullable|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'employee_code' => ['nullable', 'string', 'max:100', Rule::unique('staff_profiles', 'employee_code')->ignore($this->staffProfileId)],
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'contract', 'intern', 'casual'])],
            'employment_status' => ['required', Rule::in(['active', 'on_leave', 'suspended', 'terminated'])],
            'hire_date' => 'nullable|date',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:100',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.staff.index', [
            'staffProfiles' => StaffProfile::query()
                ->with(['user.roles', 'branch', 'module'])
                ->when(! auth()->user()?->isSuperAdmin(), function ($query) {
                    $branchIds = $this->accessibleBranches()->pluck('id');

                    $query->where(function ($query) use ($branchIds) {
                        $query->whereIn('branch_id', $branchIds)
                            ->orWhereHas('user.branchAssignments', fn ($branchQuery) => $branchQuery
                                ->whereIn('branch_id', $branchIds)
                                ->where('is_active', true));
                    });
                })
                ->when($this->search, fn ($query) => $query->where(function ($query) {
                    $query->where('employee_code', 'like', "%{$this->search}%")
                        ->orWhere('job_title', 'like', "%{$this->search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%"));
                }))
                ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterStatus, fn ($query) => $query->where('employment_status', $this->filterStatus))
                ->latest()
                ->paginate(10),
            'users' => $this->availableUsers(),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $this->branch_id ?: null),
        ]);
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

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'staff-profile-form');
    }

    public function edit(int $id): void
    {
        $profile = $this->visibleProfilesQuery()->findOrFail($id);

        $this->staffProfileId = $profile->id;
        $this->user_id = $profile->user_id;
        $this->branch_id = $profile->branch_id;
        $this->module_id = $profile->module_id;
        $this->employee_code = $profile->employee_code;
        $this->job_title = $profile->job_title;
        $this->department = $profile->department;
        $this->employment_type = $profile->employment_type;
        $this->employment_status = $profile->employment_status;
        $this->hire_date = $profile->hire_date?->toDateString();
        $this->date_of_birth = $profile->date_of_birth?->toDateString();
        $this->gender = $profile->gender;
        $this->national_id = $profile->national_id;
        $this->emergency_contact_name = $profile->emergency_contact_name;
        $this->emergency_contact_phone = $profile->emergency_contact_phone;
        $this->emergency_contact_relationship = $profile->emergency_contact_relationship;
        $this->bank_name = $profile->bank_name;
        $this->bank_account_name = $profile->bank_account_name;
        $this->bank_account_number = $profile->bank_account_number;
        $this->address = $profile->address;
        $this->notes = $profile->notes;

        $this->dispatch('open-modal', 'staff-profile-form');
    }

    public function save(): void
    {
        $this->validate();

        if ($this->branch_id) {
            $this->authorizeBranch($this->branch_id);
        }

        if ($this->module_id) {
            $this->authorizeModule($this->module_id, $this->branch_id);
        }

        StaffProfile::updateOrCreate(['id' => $this->staffProfileId], [
            'user_id' => $this->user_id,
            'branch_id' => $this->branch_id ?: null,
            'module_id' => $this->module_id ?: null,
            'employee_code' => $this->employee_code ?: null,
            'job_title' => $this->job_title,
            'department' => $this->department,
            'employment_type' => $this->employment_type,
            'employment_status' => $this->employment_status,
            'hire_date' => $this->hire_date ?: null,
            'date_of_birth' => $this->date_of_birth ?: null,
            'gender' => $this->gender,
            'national_id' => $this->national_id,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relationship' => $this->emergency_contact_relationship,
            'bank_name' => $this->bank_name,
            'bank_account_name' => $this->bank_account_name,
            'bank_account_number' => $this->bank_account_number,
            'address' => $this->address,
            'notes' => $this->notes,
        ]);

        $this->resetForm();
        $this->dispatch('close-modal', 'staff-profile-form');

        LivewireAlert::title('Staff Profile Saved')
            ->text('Staff details have been saved successfully.')
            ->success()
            ->show();
    }

    public function delete(int $id): void
    {
        LivewireAlert::title('Delete Staff Profile')
            ->text('Are you sure you want to delete this staff profile?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data): void
    {
        $this->visibleProfilesQuery()->findOrFail($data['id'])->delete();

        LivewireAlert::title('Deleted')
            ->text('Staff profile deleted successfully.')
            ->success()
            ->show();
    }

    public function resetForm(): void
    {
        $this->reset([
            'staffProfileId',
            'user_id',
            'module_id',
            'employee_code',
            'job_title',
            'department',
            'employment_type',
            'employment_status',
            'hire_date',
            'date_of_birth',
            'gender',
            'national_id',
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_relationship',
            'bank_name',
            'bank_account_name',
            'bank_account_number',
            'address',
            'notes',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->employment_type = 'full_time';
        $this->employment_status = 'active';
    }

    protected function availableUsers()
    {
        $usedUserIds = StaffProfile::query()
            ->when($this->staffProfileId, fn ($query) => $query->where('id', '!=', $this->staffProfileId))
            ->pluck('user_id');

        $branchIds = $this->accessibleBranches()->pluck('id');

        return User::query()
            ->with('roles')
            ->whereNotIn('id', $usedUserIds)
            ->when(! auth()->user()?->isSuperAdmin(), function ($query) use ($branchIds) {
                $query
                    ->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->where('name', 'Super Admin'))
                    ->whereHas('branchAssignments', fn ($branchQuery) => $branchQuery
                        ->whereIn('branch_id', $branchIds)
                        ->where('is_active', true));
            })
            ->orderBy('name')
            ->get();
    }

    protected function visibleProfilesQuery()
    {
        return StaffProfile::query()
            ->when(! auth()->user()?->isSuperAdmin(), function ($query) {
                $branchIds = $this->accessibleBranches()->pluck('id');

                $query->where(function ($query) use ($branchIds) {
                    $query->whereIn('branch_id', $branchIds)
                        ->orWhereHas('user.branchAssignments', fn ($branchQuery) => $branchQuery
                            ->whereIn('branch_id', $branchIds)
                            ->where('is_active', true));
                });
            });
    }
}
