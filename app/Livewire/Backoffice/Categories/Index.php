<?php

namespace App\Livewire\Backoffice\Categories;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';

    public $categoryId;

    public $branch_id;
    public $module_id;

    public $name;
    public $slug;
    public $description;

    public $image_url;
    public $image_upload;

    public $is_active = true;

    public $sort_order = 0;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',

            'module_id' => 'required|exists:modules,id',

            'name' => 'required|string|max:255',

            'slug' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'slug')
                    ->where(fn ($query) => $query
                        ->where('branch_id', $this->branch_id)
                        ->where('module_id', $this->module_id))
                    ->ignore($this->categoryId),
            ],

            'description' => 'nullable|string',

            'image_url' => 'nullable|string|max:2048',
            'image_upload' => 'nullable|image|max:5120',

            'is_active' => 'boolean',

            'sort_order' => 'nullable|integer',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.categories.index', [
            'categories' => Category::with(['branch', 'module'])
                ->accessible()
                ->when(
                    $this->search,
                    fn($query) =>
                    $query->where(fn ($query) => $query
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%"))
                )
                ->when(
                    $this->filterBranch,
                    fn ($query) => $query->where('branch_id', $this->filterBranch)
                )
                ->when(
                    $this->filterModule,
                    fn ($query) => $query->where('module_id', $this->filterModule)
                )
                ->orderBy('sort_order')
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

    public function updatedImageUpload(): void
    {
        $this->validateOnly('image_upload');
    }

    public function updatedName()
    {
        if (!$this->categoryId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'categoryId',
            'branch_id',
            'module_id',
            'name',
            'slug',
            'description',
            'image_url',
            'image_upload',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->is_active = true;

        $this->sort_order = 0;
    }

    public function create()
    {
        $this->resetForm();

        $this->dispatch('open-modal', 'category-form');
    }

    public function edit($id)
    {
        $category = Category::accessible()->findOrFail($id);

        $this->categoryId = $category->id;

        $this->branch_id = $category->branch_id;

        $this->module_id = $category->module_id;

        $this->name = $category->name;

        $this->slug = $category->slug;

        $this->description = $category->description;

        $this->image_url = $category->image_url;

        $this->is_active = $category->is_active;

        $this->sort_order = $category->sort_order;

        $this->dispatch('open-modal', 'category-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);
        $this->authorizeModule($this->module_id, $this->branch_id);

        $imageUrl = $this->image_url;

        if ($this->image_upload) {
            if ($this->categoryId) {
                $category = Category::accessible()->findOrFail($this->categoryId);
                $this->deleteStoredImage($category->image_url);
            }

            $imageUrl = $this->image_upload->store('categories', 'public');
        }

        Category::updateOrCreate(
            ['id' => $this->categoryId],
            [
                'branch_id' => $this->branch_id,

                'module_id' => $this->module_id,

                'name' => $this->name,

                'slug' => $this->slug,

                'description' => $this->description,

                'image_url' => $imageUrl,

                'is_active' => $this->is_active,

                'sort_order' => $this->sort_order,
            ]
        );

        $this->dispatch('close-modal', 'category-form');

        LivewireAlert::title('Category Saved')
            ->text('Category saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        LivewireAlert::title('Delete Category')
            ->text('Are you sure you want to delete this category?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data)
    {
        $category = Category::accessible()->findOrFail($data['id']);
        $this->deleteStoredImage($category->image_url);
        $category->delete();

        LivewireAlert::title('Category Deleted')
            ->text('Category deleted successfully.')
            ->success()
            ->show();
    }

    protected function deleteStoredImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
