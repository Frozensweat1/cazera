<?php

namespace App\Livewire\Backoffice\InventoryLocations;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\InventoryLocation;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';

    public $locationId;
    public $branch_id;
    public $module_id;
    public $name;
    public $code;
    public $address;
    public $type = 'other';
    public $is_active = true;
    public $notes;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventory_locations', 'code')
                    ->where(fn($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($this->locationId),
            ],
            'address' => 'nullable|string',
            'type' => 'required|in:warehouse,store,freezer,manufacturing,other',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.inventory-locations.index', [
            'locations' => InventoryLocation::with(['branch', 'module'])
                ->accessible()
                ->when($this->search, fn($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                    ->orWhere('address', 'like', "%{$this->search}%")))
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->latest()
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
            'filterModules' => $this->accessibleModules($this->filterBranch ?: null),
            'formModules' => $this->branch_id
                ? $this->accessibleModules((int) $this->branch_id)
                : collect(),
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

    public function updatedBranchId(): void
    {
        $this->module_id = null;
    }

    public function updatedName()
    {
        if (!$this->locationId) {
            $this->code = $this->code ?? str()->slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'locationId',
            'branch_id',
            'module_id',
            'name',
            'code',
            'address',
            'type',
            'notes',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->type = 'other';
        $this->is_active = true;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'inventory-location-form');
    }

    public function edit($id)
    {
        $location = InventoryLocation::accessible()->findOrFail($id);

        $this->locationId = $location->id;
        $this->branch_id = $location->branch_id;
        $this->module_id = $location->module_id;
        $this->name = $location->name;
        $this->code = $location->code;
        $this->address = $location->address;
        $this->type = $location->type;
        $this->is_active = $location->is_active;
        $this->notes = $location->notes;

        $this->dispatch('open-modal', 'inventory-location-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);

        if ($this->module_id) {
            $this->authorizeModule($this->module_id, $this->branch_id);
        }

        InventoryLocation::updateOrCreate([
            'id' => $this->locationId,
        ], [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ]);

        $this->dispatch('close-modal', 'inventory-location-form');

        LivewireAlert::title('Inventory Location Saved')
            ->text('Inventory location saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        LivewireAlert::title('Delete Inventory Location')
            ->text('Are you sure you want to delete this inventory location?')
            ->asConfirm()
            ->onConfirm('delete', ['id' => $id])
            ->show();
    }

    public function delete($id)
    {
        $id = is_array($id) ? $id['id'] : $id;
        InventoryLocation::accessible()->findOrFail($id)->delete();

        LivewireAlert::title('Inventory Location Deleted')
            ->text('Inventory location deleted successfully.')
            ->success()
            ->show();
    }
}
