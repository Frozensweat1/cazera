<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Staff Management</h1>
                <p class="text-gray-500">Manage staff employment, emergency, and payroll details linked to user accounts.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Staff Profile</x-ui.button>
        </div>

        <div class="panel">
            <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search staff..." />
                <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterModule" wire:model.live="filterModule">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="filterStatus" wire:model.live="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="on_leave">On Leave</option>
                    <option value="suspended">Suspended</option>
                    <option value="terminated">Terminated</option>
                </x-ui.select>
                <div></div>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Code</th>
                        <th>Branch</th>
                        <th>Module</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Emergency</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staffProfiles as $profile)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $profile->user?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $profile->user?->email }}</div>
                            </td>
                            <td>{{ $profile->employee_code ?: '-' }}</td>
                            <td>{{ $profile->branch?->name ?: '-' }}</td>
                            <td>{{ $profile->module?->name ?: '-' }}</td>
                            <td>
                                <div>{{ $profile->job_title ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $profile->department }}</div>
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($profile->employment_status) {
                                        'active' => 'bg-success',
                                        'on_leave' => 'bg-warning',
                                        'suspended', 'terminated' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ str($profile->employment_status)->replace('_', ' ')->headline() }}</span>
                            </td>
                            <td>
                                @if ($profile->emergency_contact_name || $profile->emergency_contact_phone)
                                    <div class="text-sm">{{ $profile->emergency_contact_name ?: 'Contact' }}</div>
                                    <div class="text-xs text-gray-500">{{ $profile->emergency_contact_phone }}</div>
                                @else
                                    <span class="text-xs text-gray-500">Not set</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <x-ui.table-dropdown>
                                    <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $profile->id }})">Edit</x-ui.table-dropdown-item>
                                    <x-ui.table-dropdown-item danger icon="trash" wire:click="delete({{ $profile->id }})">Delete</x-ui.table-dropdown-item>
                                </x-ui.table-dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-500">No staff profiles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $staffProfiles->links() }}</div>
        </div>

        <x-ui.modal name="staff-profile-form" maxWidth="6xl">
            <x-slot:title>{{ $staffProfileId ? 'Edit Staff Profile' : 'Add Staff Profile' }}</x-slot:title>

            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <x-ui.select label="User Account" name="user_id" wire:model="user_id">
                        <option value="">Select User</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input label="Employee Code" name="employee_code" wire:model="employee_code" />
                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">
                        <option value="">No Primary Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="module_id" wire:model="module_id">
                        <option value="">No Primary Module</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <x-ui.input label="Job Title" name="job_title" wire:model="job_title" />
                    <x-ui.input label="Department" name="department" wire:model="department" />
                    <x-ui.select label="Employment Type" name="employment_type" wire:model="employment_type">
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="intern">Intern</option>
                        <option value="casual">Casual</option>
                    </x-ui.select>
                    <x-ui.select label="Employment Status" name="employment_status" wire:model="employment_status">
                        <option value="active">Active</option>
                        <option value="on_leave">On Leave</option>
                        <option value="suspended">Suspended</option>
                        <option value="terminated">Terminated</option>
                    </x-ui.select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <x-ui.input label="Hire Date" type="date" name="hire_date" wire:model="hire_date" />
                    <x-ui.input label="Date of Birth" type="date" name="date_of_birth" wire:model="date_of_birth" />
                    <x-ui.input label="Gender" name="gender" wire:model="gender" />
                    <x-ui.input label="National ID" name="national_id" wire:model="national_id" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.input label="Emergency Contact" name="emergency_contact_name" wire:model="emergency_contact_name" />
                    <x-ui.input label="Emergency Phone" name="emergency_contact_phone" wire:model="emergency_contact_phone" />
                    <x-ui.input label="Relationship" name="emergency_contact_relationship" wire:model="emergency_contact_relationship" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.input label="Bank Name" name="bank_name" wire:model="bank_name" />
                    <x-ui.input label="Account Name" name="bank_account_name" wire:model="bank_account_name" />
                    <x-ui.input label="Account Number" name="bank_account_number" wire:model="bank_account_number" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.textarea label="Address" name="address" wire:model="address" />
                    <x-ui.textarea label="Notes" name="notes" wire:model="notes" />
                </div>
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'staff-profile-form')">Cancel</x-ui.button>
                    <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save Staff Profile</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>
    </div>
</div>
