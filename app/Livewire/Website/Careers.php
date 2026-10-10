<?php

namespace App\Livewire\Website;

use App\Models\CareerApplication;
use App\Models\CareerOpening;
use App\Support\WebsiteContent;
use Illuminate\Support\Facades\RateLimiter;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

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
            'company' => ['nullable', 'string', 'max:255'],
        ]);

        if (filled($this->company)) {
            return;
        }

        $rateKey = 'website-career:'.sha1((string) request()->ip());

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $this->addError('message', 'Too many applications were sent. Please wait a few minutes and try again.');

            return;
        }

        RateLimiter::hit($rateKey, 300);

        try {
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
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('message', 'We could not submit your application right now. Please try again in a moment.');

            return;
        }

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
        $page = WebsiteContent::page('careers');

        return view('livewire.website.careers', [
            'page' => $page,
            'vacancies' => WebsiteContent::careers(),
        ])->layoutData(WebsiteContent::seoData($page, 'Careers | '.$brand));
    }
}
