<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Contact Messages</h1>
                <p class="text-gray-500">Review and manage incoming website contact submissions.</p>
            </div>
            <div class="text-sm text-gray-500">Messages received from the website contact form.</div>
        </div>

        <div class="panel">
            <div class="grid gap-4 md:grid-cols-4 mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search messages..." />
                <x-ui.select label="Branch" name="filterBranch" wire:model="filterBranch">
                    <option value="">All Branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Module" name="filterModule" wire:model="filterModule">
                    <option value="">All Modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Status" name="filterStatus" wire:model="filterStatus">
                    <option value="">Any</option>
                    <option value="new">New</option>
                    <option value="read">Read</option>
                    <option value="responded">Responded</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $message->name }}</div>
                                <div class="text-xs text-gray-500">{{ $message->branch?->name ?? 'No branch' }}</div>
                            </td>
                            <td class="max-w-md truncate">{{ $message->subject ?? 'General inquiry' }}</td>
                            <td>{{ $message->email ?? '—' }}</td>
                            <td>
                                <span
                                    class="badge {{ $message->status === 'new' ? 'bg-primary' : ($message->status === 'responded' ? 'bg-success' : 'bg-warning') }}">{{ ucfirst($message->status) }}</span>
                            </td>
                            <td>{{ optional($message->received_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                <x-ui.button size="sm" icon="eye"
                                    wire:click="showMessage({{ $message->id }})">View</x-ui.button>
                                @if ($message->status !== 'read')
                                    <x-ui.button size="sm" variant="secondary" icon="check"
                                        wire:click="quickMarkAs({{ $message->id }}, 'read')">Read</x-ui.button>
                                @endif
                                @if ($message->status !== 'responded')
                                    <x-ui.button size="sm" variant="success" icon="envelope"
                                        wire:click="quickMarkAs({{ $message->id }}, 'responded')">Responded</x-ui.button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">No messages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $messages->links() }}</div>
        </div>

        <x-ui.modal name="contact-message-detail" maxWidth="2xl">
            <x-slot:title>Contact Message</x-slot:title>
            @if ($selectedMessage)
                <div class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="font-semibold text-slate-900">From</p>
                            <p>{{ $selectedMessage->name }}</p>
                            <p class="text-sm text-slate-500">{{ $selectedMessage->email ?? 'No email' }}</p>
                            <p class="text-sm text-slate-500">{{ $selectedMessage->phone ?? 'No phone' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900">Subject</p>
                            <p>{{ $selectedMessage->subject ?? 'General inquiry' }}</p>
                            <p class="mt-3 text-sm text-slate-500">Status: {{ ucfirst($selectedMessage->status) }}</p>
                        </div>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 text-slate-700">
                        {{ $selectedMessage->message }}
                    </div>
                </div>
                <x-slot:footer>
                    <div class="flex flex-wrap justify-end gap-3">
                        @if ($selectedMessage->email)
                            <a href="mailto:{{ $selectedMessage->email }}" class="btn btn-secondary inline-flex items-center gap-2">
                                <x-heroicon-o-envelope class="h-4 w-4" />
                                Email
                            </a>
                        @endif
                        @if ($selectedMessage->phone)
                            <a href="tel:{{ $selectedMessage->phone }}" class="btn btn-secondary inline-flex items-center gap-2">
                                <x-heroicon-o-phone class="h-4 w-4" />
                                Call
                            </a>
                        @endif
                        <x-ui.button type="button" variant="outline-secondary"
                            x-on:click="$dispatch('close-modal', 'contact-message-detail')">Close</x-ui.button>
                        <x-ui.button wire:click="markAs('read')" icon="check">Mark as Read</x-ui.button>
                        <x-ui.button wire:click="markAs('responded')" variant="success" icon="envelope">Mark as
                            Responded</x-ui.button>
                    </div>
                </x-slot:footer>
            @else
                <p class="text-gray-500">No message selected yet.</p>
            @endif
        </x-ui.modal>
    </div>
</div>
