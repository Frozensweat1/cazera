<?php

namespace App\Livewire\Backoffice\Website;

use App\Models\WebsitePage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;

class PagesIndex extends Component
{
    use WithFileUploads;

    public string $slug = 'about';
    public string $title = '';
    public string $eyebrow = '';
    public string $subtitle = '';
    public string $body = '';
    public string $hero_image = '';
    public $hero_image_upload;
    public string $meta_title = '';
    public string $meta_description = '';
    public bool $is_published = true;

    public array $pages = [
        'homepage' => 'Homepage',
        'about' => 'About',
        'branches' => 'Branches',
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

    public function render()
    {
        return view('livewire.backoffice.website.pages-index', [
            'records' => WebsitePage::orderBy('sort_order')->orderBy('slug')->get(),
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
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'is_published' => 'boolean',
        ]);

        $heroImage = $this->hero_image ?: null;

        if ($this->hero_image_upload) {
            $heroImage = $this->hero_image_upload->store('website-pages', 'public');
        }

        WebsitePage::updateOrCreate(['slug' => $this->slug], [
            'title' => $this->title ?: null,
            'eyebrow' => $this->eyebrow ?: null,
            'subtitle' => $this->subtitle ?: null,
            'body' => $this->body ?: null,
            'hero_image' => $heroImage,
            'meta_title' => $this->meta_title ?: null,
            'meta_description' => $this->meta_description ?: null,
            'is_published' => $this->is_published,
        ]);

        $this->hero_image = $heroImage ?: '';
        $this->hero_image_upload = null;

        LivewireAlert::title('Page Content Saved')->success()->show();
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
    }
}
