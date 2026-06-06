<?php

namespace App\Livewire\Backoffice\Website;

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsIndex extends Component
{
    use WithFileUploads;

    public $settings;
    public $business_name;
    public $tagline;
    public $email;
    public $phone;
    public $whatsapp;
    public $address;
    public $google_map_url;
    public $facebook_url;
    public $instagram_url;
    public $youtube_url;
    public $tiktok_url;
    public $x_url;
    public $meta_title;
    public $meta_description;
    public $content_json;

    public $logo;
    public $favicon;
    public $hero_background;

    public $logo_path;
    public $favicon_path;
    public $hero_background_path;

    public function mount()
    {
        $this->settings = WebsiteSetting::first();

        if ($this->settings) {
            $this->business_name = $this->settings->business_name;
            $this->tagline = $this->settings->tagline;
            $this->email = $this->settings->email;
            $this->phone = $this->settings->phone;
            $this->whatsapp = $this->settings->whatsapp;
            $this->address = $this->settings->address;
            $this->google_map_url = $this->settings->google_map_url;
            $this->facebook_url = $this->settings->facebook_url;
            $this->instagram_url = $this->settings->instagram_url;
            $this->youtube_url = $this->settings->youtube_url;
            $this->tiktok_url = $this->settings->tiktok_url;
            $this->x_url = $this->settings->x_url;
            $this->meta_title = $this->settings->meta_title;
            $this->meta_description = $this->settings->meta_description;
            $this->content_json = $this->settings->content
                ? json_encode($this->settings->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                : null;
            $this->logo_path = $this->settings->logo;
            $this->favicon_path = $this->settings->favicon;
            $this->hero_background_path = $this->settings->hero_background;
        }
    }

    protected function rules()
    {
        return [
            'business_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:64',
            'whatsapp' => 'nullable|string|max:64',
            'address' => 'nullable|string|max:1024',
            'google_map_url' => 'nullable|url|max:1024',
            'facebook_url' => 'nullable|url|max:1024',
            'instagram_url' => 'nullable|url|max:1024',
            'youtube_url' => 'nullable|url|max:1024',
            'tiktok_url' => 'nullable|url|max:1024',
            'x_url' => 'nullable|url|max:1024',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1024',
            'content_json' => 'nullable|string',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif,svg|max:5120',
            'favicon' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif,svg,ico|max:5120',
            'hero_background' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif,svg|max:5120',
        ];
    }

    public function updatedLogo()
    {
        $this->validateOnly('logo');
    }

    public function updatedFavicon()
    {
        $this->validateOnly('favicon');
    }

    public function updatedHeroBackground()
    {
        $this->validateOnly('hero_background');
    }

    public function save()
    {
        $this->validate();

        if (! $this->settings) {
            $this->settings = new WebsiteSetting();
        }

        $data = [
            'business_name' => $this->business_name,
            'tagline' => $this->tagline,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'address' => $this->address,
            'google_map_url' => $this->google_map_url,
            'facebook_url' => $this->facebook_url,
            'instagram_url' => $this->instagram_url,
            'youtube_url' => $this->youtube_url,
            'tiktok_url' => $this->tiktok_url,
            'x_url' => $this->x_url,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];

        if ($this->content_json) {
            $decoded = json_decode($this->content_json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('content_json', 'Content overrides must be valid JSON.');
                return;
            }

            $data['content'] = $decoded;
        } else {
            $data['content'] = null;
        }

        if ($this->logo) {
            $data['logo'] = $this->storeFile($this->logo, 'logo');
        }

        if ($this->favicon) {
            $data['favicon'] = $this->storeFile($this->favicon, 'favicon');
        }

        if ($this->hero_background) {
            $data['hero_background'] = $this->storeFile($this->hero_background, 'hero_background');
        }

        $this->settings->fill($data);
        $this->settings->save();

        $this->logo_path = $this->settings->logo;
        $this->favicon_path = $this->settings->favicon;
        $this->hero_background_path = $this->settings->hero_background;

        LivewireAlert::title('Website Settings Saved')
            ->text('Your website settings have been updated successfully.')
            ->success()
            ->show();
    }

    protected function storeFile($file, $prefix)
    {
        return $file->storeAs('website', sprintf('%s-%s.%s', $prefix, time(), $file->extension()), 'public');
    }

    public function render()
    {
        return view('livewire.backoffice.website.settings-index');
    }
}
