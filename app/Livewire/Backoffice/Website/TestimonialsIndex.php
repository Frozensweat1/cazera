<?php

namespace App\Livewire\Backoffice\Website;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Testimonial;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class TestimonialsIndex extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterStatus = '';

    public $testimonialId;
    public $branch_id;
    public $module_id;
    public $author_name;
    public $title;
    public $company;
    public $quote;
    public $rating = 5;
    public $is_published = true;
    public $is_featured = false;
    public $sort_order = 0;

    protected function rules()
    {
        return [
            'branch_id' => 'nullable|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'author_name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'quote' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.website.testimonials-index', [
            'testimonials' => Testimonial::with(['branch', 'module'])
                ->accessible()
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterStatus, fn($query) => $query->where('is_published', $this->filterStatus === 'published'))
                ->when($this->search, fn($query) => $query->where(function ($query) {
                    $query->where('author_name', 'like', "%{$this->search}%")
                        ->orWhere('title', 'like', "%{$this->search}%")
                        ->orWhere('company', 'like', "%{$this->search}%")
                        ->orWhere('quote', 'like', "%{$this->search}%");
                }))
                ->orderBy('sort_order')
                ->latest()
                ->paginate(12),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $this->branch_id ?: null),
        ]);
    }

    public function resetForm()
    {
        $this->reset([
            'testimonialId',
            'branch_id',
            'module_id',
            'author_name',
            'title',
            'company',
            'quote',
            'rating',
            'is_published',
            'is_featured',
            'sort_order',
        ]);

        $this->rating = 5;
        $this->is_published = true;
        $this->is_featured = false;
        $this->sort_order = 0;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'testimonial-form');
    }

    public function edit($id)
    {
        $testimonial = Testimonial::accessible()->findOrFail($id);

        $this->testimonialId = $testimonial->id;
        $this->branch_id = $testimonial->branch_id;
        $this->module_id = $testimonial->module_id;
        $this->author_name = $testimonial->author_name;
        $this->title = $testimonial->title;
        $this->company = $testimonial->company;
        $this->quote = $testimonial->quote;
        $this->rating = $testimonial->rating;
        $this->is_published = $testimonial->is_published;
        $this->is_featured = $testimonial->is_featured;
        $this->sort_order = $testimonial->sort_order;

        $this->dispatch('open-modal', 'testimonial-form');
    }

    public function save()
    {
        $this->validate();

        if ($this->branch_id) {
            $this->authorizeBranch($this->branch_id);
        }

        if ($this->module_id) {
            $this->authorizeModule($this->module_id, $this->branch_id);
        }

        Testimonial::updateOrCreate([
            'id' => $this->testimonialId,
        ], [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'author_name' => $this->author_name,
            'title' => $this->title,
            'company' => $this->company,
            'quote' => $this->quote,
            'rating' => $this->rating,
            'is_published' => $this->is_published,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
        ]);

        $this->dispatch('close-modal', 'testimonial-form');

        LivewireAlert::title('Testimonial Saved')
            ->text('Website testimonial saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function togglePublished($id): void
    {
        $testimonial = Testimonial::accessible()->findOrFail($id);
        $testimonial->update(['is_published' => ! $testimonial->is_published]);

        LivewireAlert::title($testimonial->is_published ? 'Testimonial Published' : 'Testimonial Hidden')
            ->success()
            ->show();
    }

    public function toggleFeatured($id): void
    {
        $testimonial = Testimonial::accessible()->findOrFail($id);
        $testimonial->update(['is_featured' => ! $testimonial->is_featured]);

        LivewireAlert::title($testimonial->is_featured ? 'Testimonial Featured' : 'Feature Removed')
            ->success()
            ->show();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('open-modal', 'delete-testimonial-' . $id);
    }

    public function delete($id)
    {
        Testimonial::accessible()->findOrFail($id)->delete();

        $this->dispatch('close-modal', 'delete-testimonial-' . $id);

        LivewireAlert::title('Testimonial Deleted')
            ->text('Testimonial has been removed.')
            ->success()
            ->show();
    }
}
