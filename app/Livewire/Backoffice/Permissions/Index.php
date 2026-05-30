<?php

namespace App\Livewire\Backoffice\Permissions;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $permissionId;
    public $name;
    public $guard_name = 'web';
    public $assignedRoles = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name,' . $this->permissionId,
            'guard_name' => 'required|string|max:255',
            'assignedRoles' => 'array',
            'assignedRoles.*' => 'exists:roles,id',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.permissions.index', [
            'permissions' => Permission::query()
                ->when($this->search, fn($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(10),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function resetForm()
    {
        $this->reset(['permissionId', 'name', 'guard_name', 'assignedRoles']);
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'permission-form');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);

        $this->permissionId = $permission->id;
        $this->name = $permission->name;
        $this->guard_name = $permission->guard_name;
        $this->assignedRoles = $permission->roles->pluck('id')->toArray();

        $this->dispatch('open-modal', 'permission-form');
    }

    public function save()
    {
        $this->validate();

        $permission = Permission::updateOrCreate(
            ['id' => $this->permissionId],
            ['name' => $this->name, 'guard_name' => $this->guard_name]
        );

        $permission->syncRoles($this->assignedRoles);

        $this->dispatch('close-modal', 'permission-form');

        LivewireAlert::title('Permission Saved')
            ->text('The permission has been saved and assigned to roles.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        LivewireAlert::title('Permission Deleted')
            ->text('The permission has been removed.')
            ->success()
            ->show();
    }
}
