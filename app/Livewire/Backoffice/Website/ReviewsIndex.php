<?php

namespace App\Livewire\Backoffice\Website;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\MenuItem;
use App\Models\Review;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class ReviewsIndex extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterStatus = '';

    public $reviewId;
    public $branch_id;
    public $module_id;
    public $menu_item_id;
    public $reviewer_name;
    public $email;
    public $rating = 5;
    public $review;
    public $is_approved = false;

    protected function rules()
    {
        return [
            'branch_id' => 'nullable|exists:branches,id',
            'module_id' => 'nullable|exists:modules,id',
            'menu_item_id' => 'nullable|exists:menu_items,id',
            'reviewer_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
            'is_approved' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.website.reviews-index', [
            'reviews' => Review::with(['branch', 'module', 'menuItem', 'approver'])
                ->accessible()
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterStatus, fn($query) => $query->where('is_approved', $this->filterStatus === 'approved'))
                ->when($this->search, fn($query) => $query->where(function ($query) {
                    $query->where('reviewer_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('review', 'like', "%{$this->search}%")
                        ->orWhereHas('menuItem', fn($query) => $query->where('name', 'like', "%{$this->search}%"));
                }))
                ->latest()
                ->paginate(15),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $this->branch_id ?: null),
            'menuItems' => MenuItem::accessible()->orderBy('name')->get(),
        ]);
    }

    public function resetForm()
    {
        $this->reset([
            'reviewId',
            'branch_id',
            'module_id',
            'menu_item_id',
            'reviewer_name',
            'email',
            'rating',
            'review',
            'is_approved',
        ]);

        $this->rating = 5;
        $this->is_approved = false;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'review-form');
    }

    public function edit($id)
    {
        $review = Review::accessible()->findOrFail($id);

        $this->reviewId = $review->id;
        $this->branch_id = $review->branch_id;
        $this->module_id = $review->module_id;
        $this->menu_item_id = $review->menu_item_id;
        $this->reviewer_name = $review->reviewer_name;
        $this->email = $review->email;
        $this->rating = $review->rating;
        $this->review = $review->review;
        $this->is_approved = $review->is_approved;

        $this->dispatch('open-modal', 'review-form');
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

        if ($this->menu_item_id) {
            $menuItem = MenuItem::accessible()->findOrFail($this->menu_item_id);
            abort_unless(! $this->branch_id || (int) $menuItem->branch_id === (int) $this->branch_id, 403);
            abort_unless(! $this->module_id || (int) $menuItem->module_id === (int) $this->module_id, 403);
        }

        $payload = [
            'branch_id' => $this->branch_id,
            'module_id' => $this->module_id,
            'menu_item_id' => $this->menu_item_id,
            'reviewer_name' => $this->reviewer_name,
            'email' => $this->email,
            'rating' => $this->rating,
            'review' => $this->review,
            'is_approved' => $this->is_approved,
        ];

        if ($this->is_approved) {
            $payload['approved_by'] = auth()->id();
            $payload['approved_at'] = now();
        }

        Review::updateOrCreate([
            'id' => $this->reviewId,
        ], $payload);

        $this->dispatch('close-modal', 'review-form');

        LivewireAlert::title('Review Saved')
            ->text('Review record saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function approve($id): void
    {
        Review::accessible()->findOrFail($id)->update([
            'is_approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        LivewireAlert::title('Review Approved')
            ->text('The review is now visible on the website.')
            ->success()
            ->show();
    }

    public function unapprove($id): void
    {
        Review::accessible()->findOrFail($id)->update([
            'is_approved' => false,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        LivewireAlert::title('Review Hidden')
            ->text('The review has been removed from public display.')
            ->success()
            ->show();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('open-modal', 'delete-review-' . $id);
    }

    public function delete($id)
    {
        Review::accessible()->findOrFail($id)->delete();

        $this->dispatch('close-modal', 'delete-review-' . $id);

        LivewireAlert::title('Review Deleted')
            ->text('Review has been removed.')
            ->success()
            ->show();
    }
}
