<?php

namespace App\Livewire\Backoffice\Website;

use App\Models\WebsitePage;
use App\Support\WebsiteContent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class PagesIndex extends Component
{
    use WithFileUploads;

    public string $slug = 'homepage';

    public string $title = '';

    public string $eyebrow = '';

    public string $subtitle = '';

    public string $body = '';

    public string $hero_image = '';

    public $hero_image_upload;

    public string $meta_title = '';

    public string $meta_description = '';

    public bool $is_published = true;

    public string $sections_json = '';

    public array $pages = [
        'homepage' => 'Homepage',
        'about' => 'About',
        'branches' => 'Branches',
        'menu' => 'Menu',
        'gallery' => 'Gallery',
        'events' => 'Events',
        'careers' => 'Careers',
        'contact' => 'Contact',
        'reviews' => 'Reviews',
        'mission' => 'Mission',
        'vision' => 'Vision',
    ];

    public function mount(): void
    {
        $this->loadPage();
    }

    public function updatedSlug(): void
    {
        $this->loadPage();
    }

    public function selectPage(string $slug): void
    {
        abort_unless(array_key_exists($slug, $this->pages), 404);

        $this->slug = $slug;
        $this->resetErrorBag();
        $this->loadPage();
    }

    public function render()
    {
        return view('livewire.backoffice.website.pages-index', [
            'publicUrl' => $this->publicUrl(),
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'slug' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'eyebrow' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:1000',
            'body' => 'nullable|string',
            'hero_image' => 'nullable|string|max:2048',
            'hero_image_upload' => 'nullable|image|max:5120',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:180',
            'is_published' => 'boolean',
            'sections_json' => 'nullable|string',
        ]);

        $sections = null;

        if (filled($this->sections_json)) {
            $sections = json_decode($this->sections_json, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($sections)) {
                $this->addError('sections_json', 'Section content must be a valid JSON object.');

                return;
            }
        }

        $heroImage = $this->hero_image ?: null;
        $newUpload = null;

        try {
            if ($this->hero_image_upload) {
                $heroImage = $this->hero_image_upload->store('website-pages', 'public');
                $newUpload = $heroImage;
            }

            $page = WebsitePage::query()->firstOrNew(['slug' => $this->slug]);
            $oldHeroImage = $page->hero_image;

            $page->fill([
                'title' => $this->title ?: null,
                'eyebrow' => $this->eyebrow ?: null,
                'subtitle' => $this->subtitle ?: null,
                'body' => $this->body ?: null,
                'hero_image' => $heroImage,
                'sections' => $sections,
                'meta_title' => $this->meta_title ?: null,
                'meta_description' => $this->meta_description ?: null,
                'is_published' => $this->is_published,
            ])->save();

            $this->deleteReplacedUpload($oldHeroImage, $heroImage);
            $this->hero_image = $heroImage ?: '';
            $this->hero_image_upload = null;
            WebsiteContent::flushMemoized();

            LivewireAlert::title('Page Content Saved')
                ->text('The published page and its search metadata are up to date.')
                ->success()
                ->show();
        } catch (Throwable $exception) {
            if ($newUpload) {
                Storage::disk('public')->delete($newUpload);
            }

            report($exception);
            LivewireAlert::title('Page Could Not Be Saved')
                ->text('Please try again. If the problem continues, contact support.')
                ->error()
                ->show();
        }
    }

    private function loadPage(): void
    {
        $page = WebsitePage::where('slug', $this->slug)->first();

        $this->title = $page?->title ?: '';
        $this->eyebrow = $page?->eyebrow ?: '';
        $this->subtitle = $page?->subtitle ?: '';
        $this->body = $page?->body ?: '';
        $this->hero_image = $page?->hero_image ?: '';
        $this->hero_image_upload = null;
        $this->meta_title = $page?->meta_title ?: '';
        $this->meta_description = $page?->meta_description ?: '';
        $this->is_published = $page?->is_published ?? true;
        $this->sections_json = $page?->sections
            ? json_encode($page->sections, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            : '';
    }

    private function publicUrl(): string
    {
        return match ($this->slug) {
            'homepage' => route('website.home'),
            'about', 'mission', 'vision' => route('website.about'),
            'branches' => route('website.branches'),
            'menu' => route('website.menu'),
            'gallery' => route('website.gallery'),
            'events' => route('website.events'),
            'careers' => route('website.careers'),
            'contact' => route('website.contact'),
            'reviews' => route('website.reviews'),
            default => route('website.home'),
        };
    }

    private function deleteReplacedUpload(?string $oldPath, ?string $newPath): void
    {
        if (! $oldPath || $oldPath === $newPath || Str::startsWith($oldPath, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete(ltrim(Str::after($oldPath, 'storage/'), '/'));
    }
}
