<?php

namespace App\Livewire\Website;

use App\Models\CareerApplication;
use App\Models\CareerOpening;
use App\Support\WebsiteContent;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.website')]
class Careers extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $role = '';
    public string $message = '';
    public string $company = '';
    public ?string $successMessage = null;

    public function apply(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'role' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'company' => ['nullable', 'string', 'max:1'],
        ]);

        if (filled($this->company)) {
            return;
        }

        $opening = CareerOpening::query()
            ->active()
            ->where('role', $this->role)
            ->first();

        CareerApplication::create([
            'career_opening_id' => $opening?->id,
            'branch_id' => $opening?->branch_id,
            'role' => $this->role,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'message' => $this->message,
            'status' => 'new',
            'submitted_at' => now(),
        ]);

        $this->successMessage = 'Thank you. Your application interest has been received by the hospitality team.';
        LivewireAlert::title('Application Sent')
            ->text('Your application interest has been received by the hospitality team.')
            ->success()
            ->show();
        $this->reset(['name', 'email', 'phone', 'role', 'message', 'company']);
    }

    public function render()
    {
        $brand = WebsiteContent::settings()?->business_name ?: config('app.name', 'Cazera');

        return view('livewire.website.careers', [
            'page' => WebsiteContent::page('careers'),
            'vacancies' => WebsiteContent::careers(),
        ])->title('Careers | ' . $brand);
    }
}
