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

            <form wire:submit.prevent="updateProfile" class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Name</label>
                    <input type="text" wire:model="profile_name"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                    @error('profile_name')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
                    <input type="email" wire:model="profile_email"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                    @error('profile_email')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 dark:focus:ring-white/70 dark:focus:ring-offset-[#0e1726]">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0e1726]">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Change Password</h2>

            <form wire:submit.prevent="updatePassword" class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Current password</label>
                    <input type="password" wire:model="current_password"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                    @error('current_password')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">New password</label>
                    <input type="password" wire:model="password"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                    @error('password')
                        <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Confirm new password</label>
                    <input type="password" wire:model="password_confirmation"
                        class="mt-1 w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0e1726] px-3 py-2 text-sm" />
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200 dark:focus:ring-white/70 dark:focus:ring-offset-[#0e1726]">
                        Update Password
                    </button>
                </div>
            </form>
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
