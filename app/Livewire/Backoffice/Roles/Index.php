<?php

namespace App\Livewire\Backoffice\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $roleId;
    public $name;
    public $guard_name = 'web';
    public $selectedPermissions = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->roleId,
            'guard_name' => 'required|string|max:255',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.roles.index', [
            'roles' => Role::query()
                ->when($this->search, fn($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->latest()
                ->paginate(10),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function resetForm()
    {
        $this->reset(['roleId', 'name', 'guard_name', 'selectedPermissions']);
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'role-form');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

        $this->dispatch('open-modal', 'role-form');
    }

    public function save()
    {
        $this->validate();

        $role = Role::updateOrCreate(
            ['id' => $this->roleId],
            ['name' => $this->name, 'guard_name' => $this->guard_name]
        );

        $role->syncPermissions($this->selectedPermissions);

        $this->dispatch('close-modal', 'role-form');

        LivewireAlert::title('Role Saved')
            ->text('Role permissions were updated successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        LivewireAlert::title('Role Deleted')
            ->text('The role has been removed.')
            ->success()
            ->show();
    }
}
