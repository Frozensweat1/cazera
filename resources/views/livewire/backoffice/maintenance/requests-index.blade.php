<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Maintenance Requests</h1>
                <p class="text-gray-500">Track asset issues, approvals, execution, costs, and locked maintenance records.</p>
            </div>
            <x-ui.button wire:click="create" target="create" icon="plus" loadingText="Opening...">
                New Request
            </x-ui.button>
        </div>

        <div class="panel">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search equipment..." />
                <x-ui.select name="filterStatus" wire:model.live="filterStatus">
                    <option value="">All Status</option>
                    <option value="requested">Requested</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </x-ui.select>
                <x-ui.select name="filterPriority" wire:model.live="filterPriority">
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </x-ui.select>
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
            </div>
        </div>

        <div class="panel">
            <x-ui.table>
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Est. Cost</th>
                        <th>Actual Cost</th>
                        <th>Requested</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-950">{{ $request->equipment_name }}</div>
                                <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($request->description, 55) }}</div>
                                @if ($request->is_locked)
                                    <div class="mt-1 text-xs font-semibold text-red-600">
                                        Locked by {{ $request->locker?->name ?? 'System' }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $request->branch?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $request->module?->name ?? 'No module' }}</div>
                            </td>
                            <td>{{ ucfirst($request->type) }}</td>
                            <td>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $request->getPriorityBadgeClass() }}">
                                    {{ ucfirst($request->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $request->getStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td>{{ number_format($request->estimated_cost, 2) }}</td>
                            <td>{{ $request->actual_cost ? number_format($request->actual_cost, 2) : 'N/A' }}</td>
                            <td>{{ $request->requested_date?->format('Y-m-d') }}</td>
                            <td class="text-center">
                                @if ($request->is_locked && ! $canManageRestrictedActions)
                                    <span class="text-xs font-semibold text-gray-500">Locked</span>
                                @else
                                    <x-ui.table-dropdown>
                                        @if (! $request->is_locked)
                                            @if ($request->status === 'requested')
                                                <x-ui.table-dropdown-item icon="check" wire:click="approve({{ $request->id }})">Approve</x-ui.table-dropdown-item>
                                                <x-ui.table-dropdown-item danger icon="x-mark" wire:click="openReject({{ $request->id }})">Reject</x-ui.table-dropdown-item>
                                            @elseif ($request->status === 'approved')
                                                <x-ui.table-dropdown-item icon="play" wire:click="markInProgress({{ $request->id }})">Start Work</x-ui.table-dropdown-item>
                                            @elseif ($request->status === 'in_progress' && $canManageRestrictedActions)
                                                <x-ui.table-dropdown-item icon="check-circle" wire:click="openComplete({{ $request->id }})">Complete</x-ui.table-dropdown-item>
                                            @endif
                                            @if (! in_array($request->status, ['completed', 'cancelled', 'rejected'], true))
                                                <x-ui.table-dropdown-item danger icon="no-symbol" wire:click="cancel({{ $request->id }})">Cancel</x-ui.table-dropdown-item>
                                            @endif
                                            <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $request->id }})">Edit</x-ui.table-dropdown-item>
                                        @endif

                                        @if ($canManageRestrictedActions)
                                            @if ($request->is_locked)
                                                <x-ui.table-dropdown-item icon="lock-open" wire:click="unlockRequest({{ $request->id }})">Unlock</x-ui.table-dropdown-item>
                                            @else
                                                <x-ui.table-dropdown-item icon="lock-closed" wire:click="lockRequest({{ $request->id }})">Lock</x-ui.table-dropdown-item>
                                            @endif
                                        @endif

                                        @if (! $request->is_locked)
                                            <x-ui.table-dropdown-item danger icon="trash" wire:click="confirmDelete({{ $request->id }})">Delete</x-ui.table-dropdown-item>
                                        @endif
                                    </x-ui.table-dropdown>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-gray-500">No maintenance requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $requests->links() }}</div>
        </div>

        <x-ui.modal name="maintenance-request-form" maxWidth="4xl">
            <x-slot:title>{{ $editingId ? 'Edit Maintenance Request' : 'New Maintenance Request' }}</x-slot:title>

            <div class="space-y-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">
                        <option value="">Select Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="module_id" wire:model="module_id">
                        <option value="">No module</option>
                        @foreach ($formModules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input label="Equipment / Asset" name="equipment_name" wire:model.live="equipment_name" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.select label="Type" name="type" wire:model="type">
                        <option value="preventive">Preventive</option>
                        <option value="corrective">Corrective</option>
                        <option value="inspection">Inspection</option>
                        <option value="replacement">Replacement</option>
                        <option value="repair">Repair</option>
                    </x-ui.select>
                    <x-ui.select label="Priority" name="priority" wire:model="priority">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </x-ui.select>
                    <x-ui.input label="Scheduled Date" type="date" name="scheduled_date" wire:model="scheduled_date" />
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Estimated Cost" type="number" step="0.01" name="estimated_cost" wire:model="estimated_cost" />
                    <x-ui.input label="Actual Cost" type="number" step="0.01" name="actual_cost" wire:model="actual_cost" />
                </div>

                <x-ui.textarea label="Description" name="description" wire:model="description" />
                <x-ui.textarea label="Notes" name="notes" wire:model="notes" />
            </div>

            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'maintenance-request-form')">
                        Cancel
                    </x-ui.button>
                    <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                        Save Request
                    </x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        <x-ui.modal name="maintenance-reject-modal" maxWidth="lg">
            <x-slot:title>Reject Maintenance Request</x-slot:title>
            <x-ui.textarea label="Reason" name="rejection_reason" wire:model="rejection_reason" />
            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'maintenance-reject-modal')">Cancel</x-ui.button>
                    <x-ui.button type="button" variant="danger" icon="x-mark" wire:click="reject">Reject</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

        <x-ui.modal name="maintenance-complete-modal" maxWidth="lg">
            <x-slot:title>Complete Maintenance Work</x-slot:title>
            <x-ui.input label="Actual Cost" type="number" step="0.01" name="completion_actual_cost" wire:model="completion_actual_cost" />
            <x-slot:footer>
                <div class="flex justify-end gap-3">
                    <x-ui.button type="button" variant="outline-danger" x-on:click="$dispatch('close-modal', 'maintenance-complete-modal')">Cancel</x-ui.button>
                    <x-ui.button type="button" icon="check" wire:click="markCompleted">Complete</x-ui.button>
                </div>
            </x-slot:footer>
        </x-ui.modal>

    </div>
</div>
