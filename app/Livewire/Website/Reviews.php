<?php

namespace App\Livewire\Website;

use App\Models\MenuItem;
use App\Models\Review;
use App\Models\Testimonial;
use App\Support\WebsiteContent;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.website')]
class Reviews extends Component
{
    use WithPagination;

    public $search = '';
    public $menu_item_id = '';
    public $branch_id = '';
    public $reviewer_name = '';
    public $email = '';
    public $rating = 5;
    public $review = '';
    public ?string $successMessage = null;
    public string $testimonial_author_name = '';
    public string $testimonial_title = '';
    public string $testimonial_quote = '';
    public int $testimonial_rating = 5;
    public string $testimonial_branch_id = '';
    public string $testimonial_company = '';
    public ?string $testimonialSuccessMessage = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBranchId(): void
    {
        $this->menu_item_id = '';
        $this->resetPage();
    }

    public function updatedMenuItemId(): void
    {
        $this->resetPage();
    }

    public function submitReview(): void
    {
        $this->validate([
            'reviewer_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'menu_item_id' => ['nullable', 'exists:menu_items,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['required', 'string', 'max:2000'],
        ]);

        $menuItem = $this->menu_item_id
            ? MenuItem::query()->where('status', 'available')->find($this->menu_item_id)
            : null;

        if ($this->menu_item_id && ! $menuItem) {
            $this->addError('menu_item_id', 'Please choose an available menu item.');
            return;
        }

        if ($menuItem && $this->branch_id && (int) $menuItem->branch_id !== (int) $this->branch_id) {
            $this->addError('menu_item_id', 'This menu item does not belong to the selected branch.');
            return;
        }

        Review::create([
            'branch_id' => $this->branch_id ?: $menuItem?->branch_id,
            'module_id' => $menuItem?->module_id,
            'menu_item_id' => $this->menu_item_id ?: null,
            'reviewer_name' => $this->reviewer_name,
            'email' => $this->email,
            'rating' => $this->rating,
            'review' => $this->review,
            'is_approved' => false,
        ]);

        $this->successMessage = 'Thank you. Your review has been submitted and will appear after approval.';
        LivewireAlert::title('Review Submitted')
            ->text('Thank you. Your review will appear after approval.')
            ->success()
            ->show();
        $this->reset(['reviewer_name', 'email', 'review']);
        $this->rating = 5;
    }

    public function submitTestimonial(): void
    {
        $this->validate([
            'testimonial_author_name' => ['required', 'string', 'max:255'],
            'testimonial_title' => ['nullable', 'string', 'max:255'],
            'testimonial_company' => ['nullable', 'string', 'max:255'],
            'testimonial_branch_id' => ['nullable', 'exists:branches,id'],
            'testimonial_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'testimonial_quote' => ['required', 'string', 'max:2000'],
        ]);

        Testimonial::create([
            'branch_id' => $this->testimonial_branch_id ?: null,
            'author_name' => $this->testimonial_author_name,
            'title' => $this->testimonial_title ?: 'Guest',
            'company' => $this->testimonial_company ?: null,
            'quote' => $this->testimonial_quote,
            'rating' => $this->testimonial_rating,
            'is_published' => false,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        $this->testimonialSuccessMessage = 'Thank you. Your testimonial has been submitted for moderation.';
        LivewireAlert::title('Testimonial Submitted')
            ->text('Thank you. Your testimonial will appear after approval.')
            ->success()
            ->show();

        $this->reset(['testimonial_author_name', 'testimonial_title', 'testimonial_quote', 'testimonial_branch_id', 'testimonial_company']);
        $this->testimonial_rating = 5;
    }

    public function render()
    {
        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');

        return view('livewire.website.reviews', [
            'page' => WebsiteContent::page('reviews'),
            'reviews' => Review::with('menuItem')
                ->approved()
                ->when($this->branch_id, fn($query) => $query->where('branch_id', $this->branch_id))
                ->when($this->menu_item_id, fn($query) => $query->where('menu_item_id', $this->menu_item_id))
                ->when($this->search, fn($query) => $query->where(function ($query) {
                    $query->where('reviewer_name', 'like', "%{$this->search}%")
                        ->orWhere('review', 'like', "%{$this->search}%")
                        ->orWhereHas('menuItem', fn($query) => $query->where('name', 'like', "%{$this->search}%"));
                }))
                ->latest('approved_at')
                ->paginate(12),
            'menuItems' => MenuItem::query()
                ->where('status', 'available')
                ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
                ->orderBy('name')
                ->get(),
            'branches' => WebsiteContent::branches(),
            'testimonials' => WebsiteContent::testimonials(),
        ])->title('Testimonials & Reviews | ' . $brand);
    }
}
