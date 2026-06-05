<div>
    <div class="flex flex-col gap-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0e1726]">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 overflow-hidden rounded-full bg-gray-200 flex items-center justify-center text-lg font-bold text-gray-700">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="User avatar" class="h-14 w-14 object-cover" />
                    @else
                        {{ $initials }}
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $name }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-300 break-all">
                        {{ $email }}
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0e1726]">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Update Profile</h2>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                    <input type="text" wire:model="profile_name"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                    @error('profile_name')
                        <div class="profile-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
                    <input type="email" wire:model="profile_email"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                    @error('profile_email')
                        <div class="profile-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="button"
                        wire:click="updateProfile"
                        wire:target="updateProfile"
                        wire:loading.attr="disabled"
                        class="profile-action-button">
                        <span wire:loading.remove wire:target="updateProfile">Save Profile</span>
                        <span wire:loading wire:target="updateProfile">Saving...</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0e1726]">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Change Password</h2>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Current password</label>
                    <input type="password" wire:model="current_password"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                    @error('current_password')
                        <div class="profile-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">New password</label>
                    <input type="password" wire:model="password"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                    @error('password')
                        <div class="profile-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirm new password</label>
                    <input type="password" wire:model="password_confirmation"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                </div>

                <div class="flex justify-end">
                    <button type="button"
                        wire:click="updatePassword"
                        wire:target="updatePassword"
                        wire:loading.attr="disabled"
                        class="profile-action-button">
                        <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                        <span wire:loading wire:target="updatePassword">Updating...</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0e1726]">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Staff Details</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Update your employment, emergency contact, and payroll reference details.</p>

            <div class="mt-4 space-y-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Employee Code</label>
                        <input type="text" wire:model="staff_employee_code" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                        @error('staff_employee_code') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Job Title</label>
                        <input type="text" wire:model="staff_job_title" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                        @error('staff_job_title') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Department</label>
                        <input type="text" wire:model="staff_department" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                        @error('staff_department') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Employment Type</label>
                        <select wire:model="staff_employment_type" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]">
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="intern">Intern</option>
                            <option value="casual">Casual</option>
                        </select>
                        @error('staff_employment_type') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Hire Date</label>
                        <input type="date" wire:model="staff_hire_date" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                        @error('staff_hire_date') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Date of Birth</label>
                        <input type="date" wire:model="staff_date_of_birth" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                        @error('staff_date_of_birth') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Gender</label>
                        <input type="text" wire:model="staff_gender" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                        @error('staff_gender') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">National ID</label>
                        <input type="text" wire:model="staff_national_id" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                        @error('staff_national_id') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Emergency Contact</label>
                        <input type="text" wire:model="staff_emergency_contact_name" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Emergency Phone</label>
                        <input type="text" wire:model="staff_emergency_contact_phone" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Relationship</label>
                        <input type="text" wire:model="staff_emergency_contact_relationship" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Bank Name</label>
                        <input type="text" wire:model="staff_bank_name" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Account Name</label>
                        <input type="text" wire:model="staff_bank_account_name" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Account Number</label>
                        <input type="text" wire:model="staff_bank_account_number" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Address</label>
                    <textarea wire:model="staff_address" rows="3" class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0e1726]"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button"
                        wire:click="updateStaffDetails"
                        wire:target="updateStaffDetails"
                        wire:loading.attr="disabled"
                        class="profile-action-button">
                        <span wire:loading.remove wire:target="updateStaffDetails">Save Staff Details</span>
                        <span wire:loading wire:target="updateStaffDetails">Saving...</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0e1726]">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Your Sessions</h2>
            <div class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                Active sessions (last activity) for this user.
            </div>


            <div class="mt-4 overflow-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-100 dark:border-white/10">
                            <th class="py-2 pr-4">Session ID</th>
                            <th class="py-2 pr-4">IP</th>
                            <th class="py-2 pr-4">Last Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr class="border-b border-gray-50 dark:border-white/5">
                                <td class="py-2 pr-4">
                                    <div class="font-mono text-xs break-all">{{ $session['id'] }}</div>
                                </td>
                                <td class="py-2 pr-4">
                                    <div class="text-xs">{{ $session['ip_address'] ?? '-' }}</div>
                                </td>
                                <td class="py-2 pr-4">
                                    <div class="text-xs">{{ $session['last_activity_human'] ?? '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                    No sessions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
