<?php

namespace App\Livewire\Backoffice;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\Branch;
use Illuminate\Support\Collection;

class UserProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public ?string $avatarUrl = null;
    public string $initials = '';

    // Profile update form
    public string $profile_name = '';
    public string $profile_email = '';

    // Password update form
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /** @var array<int, array<string, mixed>> */
    public array $sessions = [];


    public function mount(): void
    {
        $user = auth()->user();

        $this->name = (string) ($user->name ?? '');
        $this->email = (string) ($user->email ?? '');

        // initialize form fields
        $this->profile_name = $this->name;
        $this->profile_email = $this->email;


        // uses `profile_img` column from your User model migration
        $avatarPath = $user->profile_img ?? $user->avatar ?? null;
        $this->avatarUrl = $avatarPath ? asset('storage/' . $avatarPath) : null;

        $this->initials = $this->makeInitials($this->name, $this->email);

        // Fetch active sessions for this user
        $this->sessions = $this->fetchUserSessions($user->id);
    }

    private function makeInitials(string $name, string $email): string
    {
        $nameParts = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)));

        $initials = $nameParts->implode('');
        if ($initials !== '') {
            return $initials;
        }

        // fallback: first 2 letters of email
        $email = trim($email);
        return $email !== '' ? mb_strtoupper(mb_substr($email, 0, 2)) : '';
    }

    /**
     * Return sessions from `sessions` table.
     * Note: Laravel stores a serialized payload; we only surface safe/available fields.
     */
    private function fetchUserSessions(int $userId): array
    {
        try {
            $rows = \DB::table('sessions')
                ->where('user_id', $userId)
                ->orderByDesc('last_activity')
                ->limit(20)
                ->get(['id', 'ip_address', 'user_agent', 'last_activity']);

            return $rows->map(function ($row) {
                return [
                    'id' => $row->id,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => $row->last_activity,
                    'last_activity_human' => $row->last_activity
                        ? now()->diffForHumans(\Carbon\Carbon::createFromTimestamp((int) $row->last_activity), true)
                        : null,
                ];
            })->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function updateProfile(): void
    {
        $this->validate([
            'profile_name' => ['required', 'string', 'max:255'],
            'profile_email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $user = auth()->user();
        if (! $user) {
            return;
        }

        $user->forceFill([
            'name' => $this->profile_name,
            'email' => $this->profile_email,
        ])->save();

        $this->name = $this->profile_name;
        $this->email = $this->profile_email;
        $this->initials = $this->makeInitials($this->name, $this->email);

        $this->dispatch('notify', message: 'Profile updated successfully.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = auth()->user();
        if (! $user) {
            return;
        }

        if (! \Illuminate\Support\Facades\Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The provided password does not match your current password.');
            return;
        }

        $user->forceFill([
            'password' => \Illuminate\Support\Facades\Hash::make($this->password),
        ])->save();

        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        $this->dispatch('notify', message: 'Password updated successfully.');
    }

    public function render()
    {
        return view('livewire.backoffice.user-profile');
    }
}


