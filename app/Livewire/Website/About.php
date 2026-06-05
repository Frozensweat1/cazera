<?php

namespace App\Livewire\Website;

use App\Models\StaffProfile;
use App\Support\WebsiteContent;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.website')]
class About extends Component
{
    public function render()
    {
        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');

        return view('livewire.website.about', [
            'page' => WebsiteContent::page('about'),
            'branches' => WebsiteContent::branches(),
            'leaders' => StaffProfile::query()
                ->with(['user', 'branch', 'module'])
                ->where('employment_status', 'active')
                ->whereNotNull('job_title')
                ->latest('updated_at')
                ->limit(3)
                ->get()
                ->map(fn (StaffProfile $profile) => [
                    'name' => $profile->user?->name,
                    'role' => $profile->job_title,
                    'copy' => $profile->department
                        ? $profile->department . ($profile->branch ? ' / ' . $profile->branch->name : '')
                        : ($profile->branch?->name ?: 'Hospitality team'),
                    'image' => $profile->user?->profile_img ? WebsiteContent::assetPath($profile->user->profile_img) : null,
                    'initials' => collect(explode(' ', $profile->user?->name ?? 'Team'))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->implode('') ?: 'ST',
                ]),
        ])->title('About | ' . $brand);
    }
}
