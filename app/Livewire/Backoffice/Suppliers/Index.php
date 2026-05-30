<?php

namespace App\Livewire\Backoffice\Suppliers;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';

    public $supplierId;
    public $branch_id;
    public $module_id;
    public $name;
    public $slug;
    public $code;
    public $contact_name;
    public $email;
    public $phone;
    public $address;
    public $notes;
    public $is_active = true;
    public $sort_order = 0;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('suppliers', 'slug')
                    ->where(fn($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($this->supplierId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')
                    ->where(fn($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($this->supplierId),
            ],
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.suppliers.index', [
            'suppliers' => Supplier::with(['branch', 'module'])
                ->accessible()
                ->when(
                    $this->search,
                    fn($query) => $query->where(fn ($query) => $query
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%"))
                )
                ->latest()
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->branch_id ?: null),
        ]);
    }

    public function updatedName()
    {
        if (!$this->supplierId) {
            $this->slug =
                $this->slug ??
                str()->slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'supplierId',
            'branch_id',
            'module_id',
            'name',
            'slug',
            'code',
            'contact_name',
            'email',
            'phone',
            'address',
            'notes',
        ]);

        $this->is_active = true;
        $this->sort_order = 0;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'supplier-form');
    }

    public function edit($id)
    {
        $supplier = Supplier::accessible()->findOrFail($id);

        $this->supplierId = $supplier->id;
        $this->branch_id = $supplier->branch_id;
        $this->module_id = $supplier->module_id;
        $this->name = $supplier->name;
        $this->slug = $supplier->slug;
        $this->code = $supplier->code;
        $this->contact_name = $supplier->contact_name;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->notes = $supplier->notes;
        $this->is_active = $supplier->is_active;
        $this->sort_order = $supplier->sort_order;

        $this->dispatch('open-modal', 'supplier-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);

        if ($this->module_id) {
            $this->authorizeModule($this->module_id, $this->branch_id);
        }

        Supplier::updateOrCreate([
            'id' => $this->supplierId,
        ], [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ]);

        $this->dispatch('close-modal', 'supplier-form');

        LivewireAlert::title('Supplier Saved')
            ->text('Supplier saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('open-modal', 'delete-supplier-' . $id);
    }

    public function delete($id)
    {
        Supplier::accessible()->findOrFail($id)->delete();

        $this->dispatch('close-modal', 'delete-supplier-' . $id);

        LivewireAlert::title('Supplier Deleted')
            ->text('Supplier deleted successfully.')
            ->success()
            ->show();
    }
}
