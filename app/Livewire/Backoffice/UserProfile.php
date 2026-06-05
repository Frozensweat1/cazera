<?php

namespace App\Livewire\Backoffice;

use Livewire\Component;
use App\Models\StaffProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

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

    public ?string $staff_employee_code = null;
    public ?string $staff_job_title = null;
    public ?string $staff_department = null;
    public string $staff_employment_type = 'full_time';
    public ?string $staff_hire_date = null;
    public ?string $staff_date_of_birth = null;
    public ?string $staff_gender = null;
    public ?string $staff_national_id = null;
    public ?string $staff_emergency_contact_name = null;
    public ?string $staff_emergency_contact_phone = null;
    public ?string $staff_emergency_contact_relationship = null;
    public ?string $staff_bank_name = null;
    public ?string $staff_bank_account_name = null;
    public ?string $staff_bank_account_number = null;
    public ?string $staff_address = null;

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

        $profile = $user->staffProfile;
        $this->staff_employee_code = $profile?->employee_code;
        $this->staff_job_title = $profile?->job_title;
        $this->staff_department = $profile?->department;
        $this->staff_employment_type = $profile?->employment_type ?: 'full_time';
        $this->staff_hire_date = $profile?->hire_date?->toDateString();
        $this->staff_date_of_birth = $profile?->date_of_birth?->toDateString();
        $this->staff_gender = $profile?->gender;
        $this->staff_national_id = $profile?->national_id;
        $this->staff_emergency_contact_name = $profile?->emergency_contact_name;
        $this->staff_emergency_contact_phone = $profile?->emergency_contact_phone;
        $this->staff_emergency_contact_relationship = $profile?->emergency_contact_relationship;
        $this->staff_bank_name = $profile?->bank_name;
        $this->staff_bank_account_name = $profile?->bank_account_name;
        $this->staff_bank_account_number = $profile?->bank_account_number;
        $this->staff_address = $profile?->address;
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
            'profile_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->id())],
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

        LivewireAlert::title('Profile Updated')
            ->text('Your profile information has been saved successfully.')
            ->success()
            ->show();
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

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The provided password does not match your current password.');
            return;
        }

        $user->forceFill([
            'password' => Hash::make($this->password),
        ])->save();

        $this->current_password = '';
        $this->password = '';
        $this->password_confirmation = '';

        LivewireAlert::title('Password Updated')
            ->text('Your password has been changed successfully.')
            ->success()
            ->show();
    }

    public function updateStaffDetails(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $profileId = $user->staffProfile?->id;

        $this->validate([
            'staff_employee_code' => ['nullable', 'string', 'max:100', Rule::unique('staff_profiles', 'employee_code')->ignore($profileId)],
            'staff_job_title' => 'nullable|string|max:255',
            'staff_department' => 'nullable|string|max:255',
            'staff_employment_type' => 'required|in:full_time,part_time,contract,intern,casual',
            'staff_hire_date' => 'nullable|date',
            'staff_date_of_birth' => 'nullable|date|before:today',
            'staff_gender' => 'nullable|string|max:50',
            'staff_national_id' => 'nullable|string|max:255',
            'staff_emergency_contact_name' => 'nullable|string|max:255',
            'staff_emergency_contact_phone' => 'nullable|string|max:100',
            'staff_emergency_contact_relationship' => 'nullable|string|max:100',
            'staff_bank_name' => 'nullable|string|max:255',
            'staff_bank_account_name' => 'nullable|string|max:255',
            'staff_bank_account_number' => 'nullable|string|max:100',
            'staff_address' => 'nullable|string|max:1000',
        ]);

        StaffProfile::updateOrCreate(['user_id' => $user->id], [
            'employee_code' => $this->staff_employee_code ?: null,
            'job_title' => $this->staff_job_title,
            'department' => $this->staff_department,
            'employment_type' => $this->staff_employment_type,
            'hire_date' => $this->staff_hire_date ?: null,
            'date_of_birth' => $this->staff_date_of_birth ?: null,
            'gender' => $this->staff_gender,
            'national_id' => $this->staff_national_id,
            'emergency_contact_name' => $this->staff_emergency_contact_name,
            'emergency_contact_phone' => $this->staff_emergency_contact_phone,
            'emergency_contact_relationship' => $this->staff_emergency_contact_relationship,
            'bank_name' => $this->staff_bank_name,
            'bank_account_name' => $this->staff_bank_account_name,
            'bank_account_number' => $this->staff_bank_account_number,
            'address' => $this->staff_address,
        ]);

        LivewireAlert::title('Staff Details Updated')
            ->text('Your staff details have been saved successfully.')
            ->success()
            ->show();
    }

    public function render()
    {
        return view('livewire.backoffice.user-profile');
    }
}
