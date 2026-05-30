<?php

namespace App\Livewire\Backoffice\Users;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';

    public $userId;

    public $name;
    public $email;
    public $phone;
    public $address;
    public $password;
    public $role;
    public $profile_img;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'role' => 'required',
            'password' => $this->userId
                ? 'nullable|min:6'
                : 'required|min:6',
        ];
    }

    public function render()
    {
        $branchIds = $this->accessibleBranches()->pluck('id');

        return view('livewire.backoffice.users.index', [
            'users' => User::query()
                ->with(['roles', 'branchAssignments.branch', 'moduleAssignments.module'])
                ->when(! auth()->user()?->isSuperAdmin(), function ($query) use ($branchIds) {
                    $query
                        ->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->where('name', 'Super Admin'))
                        ->whereHas('branchAssignments', fn ($branchQuery) => $branchQuery
                            ->whereIn('branch_id', $branchIds)
                            ->where('is_active', true));
                })
                ->when($this->filterBranch, fn ($query) => $query->whereHas('branchAssignments', fn ($branchQuery) => $branchQuery
                    ->where('branch_id', $this->filterBranch)
                    ->where('is_active', true)))
                ->when($this->filterModule, fn ($query) => $query->whereHas('moduleAssignments', fn ($moduleQuery) => $moduleQuery
                    ->where('module_id', $this->filterModule)
                    ->when($this->filterBranch, fn ($branchModuleQuery) => $branchModuleQuery->where('branch_id', $this->filterBranch))
                    ->where('is_active', true)))
                ->when(
                    $this->search,
                    fn($q) =>
                    $q->where(fn ($query) => $query
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%"))
                )
                ->latest()
                ->paginate(10),

            'roles' => Role::query()
                ->when(! auth()->user()?->isSuperAdmin(), fn ($query) => $query->where('name', '!=', 'Super Admin'))
                ->orderBy('name')
                ->get(),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: null),
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
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

    public function resetForm()
    {
        $this->reset([
            'userId',
            'name',
            'email',
            'phone',
            'address',
            'password',
            'role',
            'profile_img',
        ]);
    }

    public function create()
    {
        $this->resetForm();

        $this->dispatch('open-modal', 'user-form');
    }

    public function edit($id)
    {
        $user = $this->visibleUsersQuery()->findOrFail($id);

        $this->userId = $user->id;

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->address = $user->address;

        $this->role = $user->roles->first()?->name;

        $this->dispatch('open-modal', 'user-form');
    }

    public function save()
    {
        $this->validate();

        if ($this->role === 'Super Admin' && ! auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        if ($this->userId) {
            $this->visibleUsersQuery()->findOrFail($this->userId);
        }

        $user = User::updateOrCreate(
            ['id' => $this->userId],
            [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'password' => $this->password
                    ? Hash::make($this->password)
                    : User::find($this->userId)?->password,
            ]
        );

        $user->syncRoles([$this->role]);

        $this->dispatch('close-modal', 'user-form');

        LivewireAlert::title('User Saved')
            ->text('The user has been successfully saved.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        LivewireAlert::title('Delete User')
            ->text('Are you sure you want to delete this user?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data)
    {
        $id = $data['id'];
        $user = $this->visibleUsersQuery()->findOrFail($id);

        if ($user->isSuperAdmin() && ! auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $user->delete();

        LivewireAlert::title('User Deleted')
            ->text('The user has been deleted successfully.')
            ->success()
            ->show();
    }

    protected function visibleUsersQuery()
    {
        $branchIds = $this->accessibleBranches()->pluck('id');

        return User::query()
            ->when(! auth()->user()?->isSuperAdmin(), function ($query) use ($branchIds) {
                $query
                    ->whereDoesntHave('roles', fn ($roleQuery) => $roleQuery->where('name', 'Super Admin'))
                    ->whereHas('branchAssignments', fn ($branchQuery) => $branchQuery
                        ->whereIn('branch_id', $branchIds)
                        ->where('is_active', true));
            });
    }
}
