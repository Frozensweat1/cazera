<?php

namespace App\Livewire\Backoffice\Modules;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Module;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';

    public $filterBranch = '';

    public $moduleId;

    public $branch_id;
    public $name;
    public $slug;
    public $type = 'pos';
    public $description;
    public $is_active = true;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('modules', 'slug')
                    ->where(fn ($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($this->moduleId),
            ],
            'type' => 'required|in:pos,activity',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.modules.index', [
            'modules' => Module::with('branch')
                ->when(! auth()->user()?->isSuperAdmin(), fn ($query) => $query->whereIn('branch_id', $this->accessibleBranches()->pluck('id')))
                ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
                ->when(
                    $this->search,
                    fn($q) => $q->where(fn ($query) => $query
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%"))
                )
                ->latest()
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedName()
    {
        if (!$this->moduleId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'moduleId',
            'branch_id',
            'name',
            'slug',
            'description',
        ]);

        $this->type = 'pos';
        $this->is_active = true;
    }

    public function create()
    {
        $this->resetForm();

        $this->dispatch('open-modal', 'module-form');
    }

    public function edit($id)
    {
        $module = Module::query()
            ->when(! auth()->user()?->isSuperAdmin(), fn ($query) => $query->whereIn('branch_id', $this->accessibleBranches()->pluck('id')))
            ->findOrFail($id);

        $this->moduleId = $module->id;

        $this->branch_id = $module->branch_id;
        $this->name = $module->name;
        $this->slug = $module->slug;
        $this->type = $module->type;
        $this->description = $module->description;
        $this->is_active = $module->is_active;

        $this->dispatch('open-modal', 'module-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);

        Module::updateOrCreate(
            ['id' => $this->moduleId],
            [
                'branch_id' => $this->branch_id,
                'name' => $this->name,
                'slug' => $this->slug,
                'type' => $this->type,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]
        );

        $this->dispatch('close-modal', 'module-form');

        LivewireAlert::title('Module Saved')
            ->text('The module has been successfully saved.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        Module::query()
            ->when(! auth()->user()?->isSuperAdmin(), fn ($query) => $query->whereIn('branch_id', $this->accessibleBranches()->pluck('id')))
            ->findOrFail($id)
            ->delete();

        LivewireAlert::title('Module Deleted')
            ->text('The module has been deleted successfully.')
            ->success()
            ->show();
    }
}
