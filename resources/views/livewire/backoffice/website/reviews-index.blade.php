<div>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Website Reviews</h1>
                <p class="text-gray-500">Manage customer ratings and website reviews.</p>
            </div>
            <x-ui.button icon="plus" wire:click="create">Add Review</x-ui.button>
        </div>

        <div class="panel">
            <div class="grid gap-4 md:grid-cols-5 mb-5">
                <x-ui.input name="search" wire:model.live="search" placeholder="Search reviews..." />
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
                <x-ui.select label="Menu Item" name="" wire:model="menu_item_id">
                    <option value="">All Items</option>
                    @foreach ($menuItems as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Status" name="filterStatus" wire:model="filterStatus">
                    <option value="">Any</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                </x-ui.select>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Review</th>
                        <th>Rating</th>
                        <th>Menu Item</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $review->reviewer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $review->email }}</div>
                            </td>
                            <td class="max-w-md truncate">{{ \Illuminate\Support\Str::limit($review->review, 80) }}</td>
                            <td>{{ $review->rating }}/5</td>
                            <td>{{ $review->menuItem?->name ?? 'General' }}</td>
                            <td>
                                @if ($review->is_approved)
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($review->is_approved)
                                    <x-ui.button size="sm" variant="secondary" icon="eye-slash"
                                        wire:click="unapprove({{ $review->id }})">Hide</x-ui.button>
                                @else
                                    <x-ui.button size="sm" variant="success" icon="check-circle"
                                        wire:click="approve({{ $review->id }})">Approve</x-ui.button>
                                @endif
                                <x-ui.button size="sm" icon="pencil-square"
                                    wire:click="edit({{ $review->id }})">Edit</x-ui.button>
                                <x-ui.button size="sm" variant="danger" icon="trash"
                                    wire:click="confirmDelete({{ $review->id }})">Delete</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">No reviews found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mt-5">{{ $reviews->links() }}</div>
        </div>

        <x-ui.modal name="review-form" maxWidth="2xl">
            <x-slot:title>{{ $reviewId ? 'Edit Review' : 'Create Review' }}</x-slot:title>
            <div class="space-y-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.select label="Branch" name="branch_id" wire:model="branch_id">
                        <option value="">None</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Module" name="module_id" wire:model="module_id">
                        <option value="">None</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select label="Menu Item" name="menu_item_id" wire:model="menu_item_id">
                        <option value="">General Review</option>
                        @foreach ($menuItems as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input label="Reviewer Name" name="reviewer_name" wire:model.live="reviewer_name" />
                    <x-ui.input label="Email" name="email" wire:model.live="email" />
                    <x-ui.input label="Rating" name="rating" wire:model.live="rating" type="number" min="1"
                        max="5" />
                </div>

                <x-ui.textarea label="Review" name="review" wire:model.live="review" />
                <x-ui.checkbox label="Approved" name="is_approved" wire:model="is_approved" />

                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'review-form')">Cancel</x-ui.button>
                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">Save
                            Review</x-ui.button>
                    </div>
                </x-slot:footer>
            </div>
        </x-ui.modal>

        @foreach ($reviews as $review)
            <x-ui.modal name="delete-review-{{ $review->id }}" maxWidth="sm">
                <x-slot:title>Delete Review</x-slot:title>
                <p>Are you sure you want to delete the review from <strong>{{ $review->reviewer_name }}</strong>?</p>
                <x-slot:footer>
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="button" variant="outline-secondary"
                            x-on:click="$dispatch('close-modal', 'delete-review-{{ $review->id }}')">Cancel</x-ui.button>
                        <x-ui.button wire:click="delete({{ $review->id }})" variant="danger">Delete</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>
        @endforeach
    </div>
</div>
