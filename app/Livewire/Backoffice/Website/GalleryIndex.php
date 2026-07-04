<?php

namespace App\Livewire\Backoffice\Website;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\GalleryItem;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Throwable;

class GalleryIndex extends Component
{
    use HasBranchScope;
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $filterCategory = '';
    public string $filterStatus = '';
    public $galleryId;
    public $branch_id = '';
    public string $title = '';
    public string $slug = '';
    public string $category = 'ambiance';
    public string $type = 'image';
    public string $image = '';
    public $image_upload;
    public string $video_url = '';
    public string $description = '';
    public bool $is_featured = false;
    public bool $is_published = true;
    public int $sort_order = 0;

    public array $categories = ['ambiance', 'food', 'events', 'nightlife', 'vip', 'interiors'];

    protected array $socialVideoDomains = [
        'youtube.com',
        'youtu.be',
        'vimeo.com',
        'instagram.com',
        'facebook.com',
        'fb.watch',
        'tiktok.com',
        'x.com',
        'twitter.com',
        'linkedin.com',
    ];

    protected function rules(): array
    {
        return [
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:gallery_items,slug,' . $this->galleryId,
            'category' => 'required|string|max:80',
            'type' => 'required|in:image,video',
            'image' => [
                Rule::requiredIf($this->type === 'image' && ! $this->image_upload),
                'nullable',
                'string',
                'max:2048',
            ],
            'image_upload' => [
                Rule::requiredIf($this->type === 'image' && blank($this->image)),
                'nullable',
                'image',
                'max:5120',
            ],
            'video_url' => [
                Rule::requiredIf($this->type === 'video'),
                'nullable',
                'url',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($this->type === 'video' && ! $this->isAllowedSocialVideoUrl((string) $value)) {
                        $fail('Video URL must be from YouTube, Vimeo, Instagram, Facebook, TikTok, X/Twitter, or LinkedIn.');
                    }
                },
            ],
            'description' => 'nullable|string|max:1000',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.website.gallery-index', [
            'items' => GalleryItem::with('branch')
                ->accessible()
                ->when($this->filterCategory, fn ($query) => $query->where('category', $this->filterCategory))
                ->when($this->filterStatus, fn ($query) => $query->where('is_published', $this->filterStatus === 'published'))
                ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                    ->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->paginate(12),
            'branches' => $this->accessibleBranches(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'gallery-form');
    }

    public function edit(int $id): void
    {
        $item = GalleryItem::accessible()->findOrFail($id);
        $this->galleryId = $item->id;
        $this->branch_id = $item->branch_id ?: '';
        $this->title = $item->title;
        $this->slug = $item->slug ?: '';
        $this->category = $item->category;
        $this->type = $item->type;
        $this->image = $item->image ?: '';
        $this->video_url = $item->video_url ?: '';
        $this->description = $item->description ?: '';
        $this->is_featured = (bool) $item->is_featured;
        $this->is_published = (bool) $item->is_published;
        $this->sort_order = (int) $item->sort_order;
        $this->dispatch('open-modal', 'gallery-form');
    }

    public function save(): void
    {
        try {
            $this->slug = $this->slug ?: Str::slug($this->title);
            $this->validate();

            $image = $this->image ?: null;

            if ($this->image_upload) {
                try {
                    $image = $this->image_upload->store('gallery', 'public');
                } catch (Throwable $e) {
                    Log::error('GalleryIndex::save image store failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    LivewireAlert::title('Image Upload Failed')
                        ->text('Unable to store uploaded image. Please try a different file or use an external URL.')
                        ->error()
                        ->show();
                    return;
                }
            }

            GalleryItem::updateOrCreate(['id' => $this->galleryId], [
                'branch_id' => $this->branch_id ?: null,
                'title' => $this->title,
                'slug' => $this->slug,
                'category' => $this->category,
                'type' => $this->type,
                'image' => $image,
                'video_url' => $this->type === 'video' ? $this->normalizedVideoUrl() : null,
                'description' => $this->description ?: null,
                'is_featured' => $this->is_featured,
                'is_published' => $this->is_published,
                'sort_order' => $this->sort_order,
            ]);

            $this->dispatch('close-modal', 'gallery-form');
            LivewireAlert::title('Gallery Saved')->success()->show();
            $this->resetForm();
        } catch (Throwable $e) {
            Log::error('GalleryIndex::save failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'galleryId' => $this->galleryId ?? null]);
            LivewireAlert::title('Error')
                ->text('Unable to save media. Please try again or contact support.')
                ->error()
                ->show();
        }
    }

    public function delete(int $id): void
    {
        GalleryItem::accessible()->findOrFail($id)->delete();
        LivewireAlert::title('Gallery Item Deleted')->success()->show();
    }

    private function resetForm(): void
    {
        $this->reset(['galleryId', 'branch_id', 'title', 'slug', 'image', 'image_upload', 'video_url', 'description']);
        $this->category = 'ambiance';
        $this->type = 'image';
        $this->is_featured = false;
        $this->is_published = true;
        $this->sort_order = 0;
    }

    protected function isAllowedSocialVideoUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return false;
        }

        $host = preg_replace('/^(www\.|m\.)/', '', $host);

        return collect($this->socialVideoDomains)
            ->contains(fn (string $domain) => $host === $domain || Str::endsWith($host, '.' . $domain));
    }

    protected function normalizedVideoUrl(): ?string
    {
        $url = trim($this->video_url);

        return $url !== '' ? $url : null;
    }
}
