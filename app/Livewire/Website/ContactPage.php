<?php

namespace App\Livewire\Website;

use App\Models\ContactMessage;
use App\Support\WebsiteContent;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.website')]
class ContactPage extends Component
{
    public $name;

    public $email;

    public $phone;

    public $branch_id = '';

    public $inquiry_category = 'General inquiry';

    public $subject;

    public $message;

    public $company;

    public $successMessage;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|required_without:phone|email|max:255',
            'phone' => 'nullable|required_without:email|string|max:32',
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('is_active', true)],
            'inquiry_category' => 'nullable|string|max:80',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:3000',
            'company' => 'nullable|string|max:255',
        ];
    }

    public function submit()
    {
        $this->validate();

        if (filled($this->company)) {
            return;
        }

        $rateKey = 'website-contact:'.sha1((string) request()->ip());

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $this->addError('message', 'Too many messages were sent. Please wait a minute and try again.');

            return;
        }

        RateLimiter::hit($rateKey, 60);

        try {
            ContactMessage::create([
                'branch_id' => $this->branch_id ?: null,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'subject' => trim(($this->inquiry_category ? "{$this->inquiry_category}: " : '').($this->subject ?: 'Website inquiry')),
                'message' => $this->message,
                'status' => 'new',
                'received_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('message', 'We could not send your message right now. Please try again in a moment.');

            return;
        }

        $this->successMessage = 'Thank you for reaching out. Your message has been submitted.';
        LivewireAlert::title('Message Sent')
            ->text('Thank you for reaching out. Our hospitality team will respond soon.')
            ->success()
            ->show();
        $this->reset(['name', 'email', 'phone', 'branch_id', 'inquiry_category', 'subject', 'message', 'company']);
        $this->inquiry_category = 'General inquiry';
    }

    public function render()
    {
        $settings = WebsiteContent::settings();
        $page = WebsiteContent::page('contact');

        return view('livewire.website.contact-page', [
            'settings' => $settings,
            'page' => $page,
            'branches' => WebsiteContent::branches(),
        ])->layoutData(WebsiteContent::seoData(
            $page,
            'Contact | '.($settings?->business_name ?: config('app.name', 'Cazera')),
        ));
    }
}
