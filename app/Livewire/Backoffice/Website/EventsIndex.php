<?php

namespace App\Livewire\Backoffice\Website;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\WebsiteEvent;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class EventsIndex extends Component
{
    use HasBranchScope;
    use WithPagination;

    public string $search = '';
    public string $filterBranch = '';
    public string $filterStatus = '';
    public $eventId;
    public $branch_id = '';
    public string $title = '';
    public string $slug = '';
    public string $tag = '';
    public string $date_label = '';
    public string $starts_at = '';
    public string $ends_at = '';
    public string $description = '';
    public string $body = '';
    public string $image = '';
    public bool $is_featured = false;
    public bool $is_published = true;
    public int $sort_order = 0;

    protected function rules(): array
    {
        return [
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:website_events,slug,' . $this->eventId,
            'tag' => 'nullable|string|max:255',
            'date_label' => 'nullable|string|max:255',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'description' => 'nullable|string|max:1000',
            'body' => 'nullable|string',
            'image' => 'nullable|string|max:2048',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.website.events-index', [
            'events' => WebsiteEvent::with('branch')
                ->accessible()
                ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterStatus, fn ($query) => $query->where('is_published', $this->filterStatus === 'published'))
                ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                    ->where('title', 'like', "%{$this->search}%")
                    ->orWhere('tag', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")))
                ->orderByDesc('is_featured')
                ->orderByRaw('starts_at IS NULL, starts_at ASC')
                ->paginate(12),
            'branches' => $this->accessibleBranches(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'website-event-form');
    }

    public function edit(int $id): void
    {
        $event = WebsiteEvent::accessible()->findOrFail($id);
        $this->eventId = $event->id;
        $this->branch_id = $event->branch_id ?: '';
        $this->title = $event->title;
        $this->slug = $event->slug;
        $this->tag = $event->tag ?: '';
        $this->date_label = $event->date_label ?: '';
        $this->starts_at = $event->starts_at?->format('Y-m-d\TH:i') ?: '';
        $this->ends_at = $event->ends_at?->format('Y-m-d\TH:i') ?: '';
        $this->description = $event->description ?: '';
        $this->body = $event->body ?: '';
        $this->image = $event->image ?: '';
        $this->is_featured = (bool) $event->is_featured;
        $this->is_published = (bool) $event->is_published;
        $this->sort_order = (int) $event->sort_order;
        $this->dispatch('open-modal', 'website-event-form');
    }

    public function save(): void
    {
        $this->slug = $this->slug ?: Str::slug($this->title);
        $this->validate();

        if ($this->branch_id) {
            $this->authorizeBranch($this->branch_id);
        }

        WebsiteEvent::updateOrCreate(['id' => $this->eventId], [
            'branch_id' => $this->branch_id ?: null,
            'title' => $this->title,
            'slug' => $this->slug,
            'tag' => $this->tag ?: null,
            'date_label' => $this->date_label ?: null,
            'starts_at' => $this->starts_at ?: null,
            'ends_at' => $this->ends_at ?: null,
            'description' => $this->description ?: null,
            'body' => $this->body ?: null,
            'image' => $this->image ?: null,
            'is_featured' => $this->is_featured,
            'is_published' => $this->is_published,
            'sort_order' => $this->sort_order,
        ]);

        $this->dispatch('close-modal', 'website-event-form');
        LivewireAlert::title('Event Saved')->text('Website event content has been saved.')->success()->show();
        $this->resetForm();
    }

    public function togglePublished(int $id): void
    {
        $event = WebsiteEvent::accessible()->findOrFail($id);
        $event->update(['is_published' => ! $event->is_published]);
        LivewireAlert::title('Event Updated')->success()->show();
    }

    public function delete(int $id): void
    {
        WebsiteEvent::accessible()->findOrFail($id)->delete();
        LivewireAlert::title('Event Deleted')->success()->show();
    }

    private function resetForm(): void
    {
        $this->reset(['eventId', 'branch_id', 'title', 'slug', 'tag', 'date_label', 'starts_at', 'ends_at', 'description', 'body', 'image']);
        $this->is_featured = false;
        $this->is_published = true;
        $this->sort_order = 0;
    }
}
