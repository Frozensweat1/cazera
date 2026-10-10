<?php

namespace App\Livewire\Backoffice\Branches;

use App\Models\Branch;
use App\Support\WebsiteContent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $search = '';

    public $branchId;

    public $name;

    public $slug;

    public $location;

    public $phone;

    public $email;

    public $latitude;

    public $longitude;

    public $is_active = true;

    public $short_description;

    public $description;

    public $hours;

    public $whatsapp;

    public $featured_image;

    public $hero_image;

    public $featured_image_upload;

    public $hero_image_upload;

    public $map_url;

    public $directions_url;

    public $tags_text;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:branches,slug,'.$this->branchId,
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'boolean',
            'short_description' => 'nullable|string|max:300',
            'description' => 'nullable|string|max:2000',
            'hours' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:32',
            'featured_image' => 'nullable|string|max:2048',
            'hero_image' => 'nullable|string|max:2048',
            'featured_image_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif|max:5120',
            'hero_image_upload' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif|max:8192',
            'map_url' => [
                'nullable',
                'url:http,https',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (filled($value) && ! WebsiteContent::isGoogleMapsEmbedUrl((string) $value)) {
                        $fail('The map URL must be a Google Maps embed URL.');
                    }
                },
            ],
            'directions_url' => 'nullable|url:http,https|max:2048',
            'tags_text' => 'nullable|string|max:255',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.branches.index', [
            'branches' => Branch::query()
                ->when(
                    $this->search,
                    fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('location', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                )
                ->latest()
                ->paginate(10),
        ]);
    }

    public function updatedName()
    {
        if (! $this->branchId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'branchId',
            'name',
            'slug',
            'location',
            'phone',
            'email',
            'latitude',
            'longitude',
            'short_description',
            'description',
            'hours',
            'whatsapp',
            'featured_image',
            'hero_image',
            'featured_image_upload',
            'hero_image_upload',
            'map_url',
            'directions_url',
            'tags_text',
        ]);

        $this->is_active = true;
    }

    public function create()
    {
        $this->resetForm();

        $this->dispatch('open-modal', 'branch-form');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);

        $this->branchId = $branch->id;

        $this->name = $branch->name;
        $this->slug = $branch->slug;
        $this->location = $branch->location;
        $this->phone = $branch->phone;
        $this->email = $branch->email;
        $this->latitude = $branch->latitude;
        $this->longitude = $branch->longitude;
        $this->is_active = $branch->is_active;
        $settings = $branch->settings ?? [];
        $this->short_description = data_get($settings, 'short_description');
        $this->description = data_get($settings, 'description');
        $this->hours = data_get($settings, 'hours');
        $this->whatsapp = data_get($settings, 'whatsapp');
        $this->featured_image = data_get($settings, 'featured_image');
        $this->hero_image = data_get($settings, 'hero_image');
        $this->map_url = data_get($settings, 'map_url');
        $this->directions_url = data_get($settings, 'directions_url');
        $this->tags_text = implode(', ', data_get($settings, 'tags', []));

        $this->dispatch('open-modal', 'branch-form');
    }

    public function save()
    {
        $this->validate();

        $branch = $this->branchId ? Branch::findOrFail($this->branchId) : new Branch;
        $settings = $branch->settings ?? [];
        $newUploads = [];

        try {
            $featuredImage = $this->featured_image ?: null;
            $heroImage = $this->hero_image ?: null;

            if ($this->featured_image_upload) {
                $featuredImage = $this->featured_image_upload->store('branches', 'public');
                $newUploads[] = $featuredImage;
            }

            if ($this->hero_image_upload) {
                $heroImage = $this->hero_image_upload->store('branches', 'public');
                $newUploads[] = $heroImage;
            }

            $oldFeaturedImage = data_get($settings, 'featured_image');
            $oldHeroImage = data_get($settings, 'hero_image');

            $branch->fill([
                'name' => $this->name,
                'slug' => $this->slug,
                'location' => $this->location ?: null,
                'phone' => $this->phone ?: null,
                'email' => $this->email ?: null,
                'latitude' => filled($this->latitude) ? $this->latitude : null,
                'longitude' => filled($this->longitude) ? $this->longitude : null,
                'is_active' => $this->is_active,
                'settings' => array_merge($settings, [
                    'short_description' => $this->short_description ?: null,
                    'description' => $this->description ?: null,
                    'hours' => $this->hours ?: null,
                    'whatsapp' => $this->whatsapp ?: null,
                    'featured_image' => $featuredImage,
                    'hero_image' => $heroImage,
                    'map_url' => $this->map_url ?: null,
                    'directions_url' => $this->directions_url ?: null,
                    'tags' => collect(explode(',', (string) $this->tags_text))
                        ->map(fn (string $tag) => trim($tag))
                        ->filter()
                        ->unique(fn (string $tag) => Str::lower($tag))
                        ->take(6)
                        ->values()
                        ->all(),
                ]),
            ])->save();

            $this->deleteReplacedUpload($oldFeaturedImage, $featuredImage);
            $this->deleteReplacedUpload($oldHeroImage, $heroImage);

            $this->dispatch('close-modal', 'branch-form');

            LivewireAlert::title('Branch Saved')
                ->text('Operational and public website details are now up to date.')
                ->success()
                ->show();

            $this->resetForm();
        } catch (Throwable $exception) {
            foreach ($newUploads as $path) {
                Storage::disk('public')->delete($path);
            }

            report($exception);
            LivewireAlert::title('Branch Could Not Be Saved')
                ->text('Please try again. If the problem continues, contact support.')
                ->error()
                ->show();
        }
    }

    public function delete($id)
    {
        LivewireAlert::title('Delete Branch')
            ->text('Are you sure you want to delete this branch?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data)
    {
        $id = $data['id'];
        Branch::findOrFail($id)->delete();

        LivewireAlert::title('Branch Deleted')
            ->text('The branch has been deleted successfully.')
            ->success()
            ->show();
    }

    private function deleteReplacedUpload(?string $oldPath, ?string $newPath): void
    {
        if (! $oldPath || $oldPath === $newPath || Str::startsWith($oldPath, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete(ltrim(Str::after($oldPath, 'storage/'), '/'));
    }
}
