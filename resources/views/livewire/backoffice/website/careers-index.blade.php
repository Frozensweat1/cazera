<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div><h1 class="text-2xl font-bold">Website Careers</h1><p class="text-gray-500">Manage vacancies and review public career applications.</p></div>
            <x-ui.button icon="plus" wire:click="create">Add Opening</x-ui.button>
        </div>
        <div class="panel">
            <div class="mb-5 grid gap-4 md:grid-cols-[1fr_auto_auto]">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search careers..." />
                <x-ui.button type="button" variant="{{ $tab === 'openings' ? 'primary' : 'secondary' }}" wire:click="$set('tab', 'openings')">Openings</x-ui.button>
                <x-ui.button type="button" variant="{{ $tab === 'applications' ? 'primary' : 'secondary' }}" wire:click="$set('tab', 'applications')">Applications</x-ui.button>
            </div>
            @if ($tab === 'openings')
                <x-ui.table><thead><tr><th>Role</th><th>Branch</th><th>Type</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>
                    @forelse ($openings as $opening)
                        <tr><td><div class="font-semibold">{{ $opening->role }}</div><div class="text-xs text-gray-500">{{ $opening->summary }}</div></td><td>{{ $opening->branch?->name ?? $opening->location ?? 'All branches' }}</td><td>{{ $opening->employment_type }}</td><td><span class="badge {{ $opening->is_active ? 'bg-success' : 'bg-warning' }}">{{ $opening->is_active ? 'Active' : 'Inactive' }}</span></td><td class="text-center"><x-ui.button size="sm" icon="pencil-square" wire:click="edit({{ $opening->id }})">Edit</x-ui.button></td></tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-gray-500">No openings found.</td></tr>
                    @endforelse
                </tbody></x-ui.table><div class="mt-5">{{ $openings->links() }}</div>
            @else
                <x-ui.table><thead><tr><th>Applicant</th><th>Role</th><th>Message</th><th>Status</th><th class="text-center">Actions</th></tr></thead><tbody>
                    @forelse ($applications as $application)
                        <tr><td><div class="font-semibold">{{ $application->name }}</div><div class="text-xs text-gray-500">{{ $application->email }} {{ $application->phone ? '/ '.$application->phone : '' }}</div></td><td>{{ $application->role }}</td><td class="max-w-md truncate">{{ $application->message }}</td><td><span class="badge bg-primary">{{ str($application->status)->headline() }}</span></td><td class="text-center"><div class="flex justify-center gap-2"><x-ui.button size="sm" wire:click="updateApplicationStatus({{ $application->id }}, 'reviewing')">Reviewing</x-ui.button><x-ui.button size="sm" variant="success" wire:click="updateApplicationStatus({{ $application->id }}, 'shortlisted')">Shortlist</x-ui.button><x-ui.button size="sm" variant="danger" wire:click="updateApplicationStatus({{ $application->id }}, 'closed')">Close</x-ui.button></div></td></tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-gray-500">No applications found.</td></tr>
                    @endforelse
                </tbody></x-ui.table><div class="mt-5">{{ $applications->links() }}</div>
            @endif
        </div>

        <x-ui.modal name="career-opening-form" maxWidth="3xl">
            <x-slot:title>{{ $openingId ? 'Edit Opening' : 'Create Opening' }}</x-slot:title>
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.select label="Branch" name="branch_id" wire:model="branch_id"><option value="">All branches</option>@foreach ($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</x-ui.select>
                <x-ui.input label="Role" name="role" wire:model.live="role" />
                <x-ui.input label="Slug" name="slug" wire:model.live="slug" />
                <x-ui.input label="Location" name="location" wire:model.live="location" />
                <x-ui.input label="Employment Type" name="employment_type" wire:model.live="employment_type" />
                <x-ui.input label="Sort Order" type="number" name="sort_order" wire:model.live="sort_order" />
                <div class="md:col-span-2"><x-ui.textarea label="Summary" name="summary" wire:model.live="summary" /></div>
                <div class="md:col-span-2"><x-ui.textarea label="Description" name="description" wire:model.live="description" /></div>
                <div class="md:col-span-2"><x-ui.textarea label="Requirements (one per line)" name="requirementsText" wire:model.live="requirementsText" /></div>
                <x-ui.checkbox label="Active" name="is_active" wire:model="is_active" />
            </div>
            <x-slot:footer><div class="flex justify-end gap-3"><x-ui.button type="button" variant="outline-secondary" x-on:click="$dispatch('close-modal', 'career-opening-form')">Cancel</x-ui.button><x-ui.button wire:click="save" icon="check">Save Opening</x-ui.button></div></x-slot:footer>
        </x-ui.modal>
    </div>
</div>
