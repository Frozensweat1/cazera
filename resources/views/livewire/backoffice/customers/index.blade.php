<div>
    {{-- Simplicity is the essence of happiness. - Cedric Bledsoe --}}
    <div class="space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold">
                    Customers
                </h1>

                <p class="text-gray-500">
                    Manage customers and loyalty records
                </p>
            </div>

            <x-ui.button icon="plus" wire:click="create">
                Add Customer
            </x-ui.button>

        </div>

        <!-- TABLE -->
        <div class="panel">

            <div class="mb-5">

                <x-ui.input name="search" wire:model.live="search" placeholder="Search customers..." />

            </div>

            <x-ui.table>

                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Debt</th>
                        <th>Status</th>
                        <th>Last Order</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($customers as $customer)
                        <tr>

                            <td>

                                <div>
                                    <p class="font-semibold">
                                        {{ $customer->name }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        {{ $customer->email }}
                                    </p>
                                </div>

                            </td>

                            <td>
                                {{ $customer->phone }}
                            </td>

                            <td>

                                @if ($customer->customer_type === 'vip')
                                    <span class="badge bg-warning">
                                        VIP
                                    </span>
                                @elseif ($customer->customer_type === 'corporate')
                                    <span class="badge bg-primary">
                                        Corporate
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        Regular
                                    </span>
                                @endif

                            </td>

                            <td>
                                {{ number_format($customer->total_orders) }}
                            </td>

                            <td>
                                {{ number_format($customer->total_spent, 2) }}
                            </td>

                            <td>

                                @if ($customer->total_debt > 0)
                                    <span class="text-danger font-semibold">
                                        {{ number_format($customer->total_debt, 2) }}
                                    </span>
                                @else
                                    <span class="text-success">
                                        0.00
                                    </span>
                                @endif

                            </td>

                            <td>

                                @if ($customer->status === 'active')
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                @elseif ($customer->status === 'inactive')
                                    <span class="badge bg-warning">
                                        Inactive
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Banned
                                    </span>
                                @endif

                            </td>

                            <td>
                                {{ $customer->last_order_at?->diffForHumans() ?? '-' }}
                            </td>

                            <td class="text-center">

                                <x-ui.table-dropdown>

                                    <x-ui.table-dropdown-item icon="clock"
                                        href="{{ route('backoffice.customers.history', ['customer' => $customer->id]) }}">
                                        History
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $customer->id }})">
                                        Edit
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item danger icon="trash" wire:click="delete({{ $customer->id }})">
                                        Delete
                                    </x-ui.table-dropdown-item>

                                </x-ui.table-dropdown>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9" class="text-center py-10 text-gray-500">
                                No customers found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </x-ui.table>

            <div class="mt-5">
                {{ $customers->links() }}
            </div>

        </div>

        <!-- MODAL -->
        <x-ui.modal name="customer-form" maxWidth="2xl">

            <x-slot:title>
                {{ $customerId ? 'Edit Customer' : 'Create Customer' }}
            </x-slot:title>

            <div class="space-y-5">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-ui.select label="Branch" name="branch_id" wire:model="branch_id">

                        <option value="">
                            Select Branch
                        </option>

                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                    <x-ui.select label="Customer Type" name="customer_type" wire:model="customer_type">

                        <option value="regular">
                            Regular
                        </option>

                        <option value="vip">
                            VIP
                        </option>

                        <option value="corporate">
                            Corporate
                        </option>

                    </x-ui.select>

                    <x-ui.input label="Customer Name" name="name" wire:model="name" />

                    <x-ui.input label="Email" type="email" name="email" wire:model="email" />

                    <x-ui.input label="Phone" name="phone" wire:model="phone" />

                    <x-ui.select label="Status" name="status" wire:model="status">

                        <option value="active">
                            Active
                        </option>

                        <option value="inactive">
                            Inactive
                        </option>

                        <option value="banned">
                            Banned
                        </option>

                    </x-ui.select>

                </div>

                <x-ui.textarea label="Address" name="address" wire:model="address" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-ui.input label="Latitude" name="latitude" wire:model="latitude" />

                    <x-ui.input label="Longitude" name="longitude" wire:model="longitude" />

                </div>

                <x-slot:footer>

                    <div class="flex justify-end gap-3">

                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'customer-form')">
                            Cancel
                        </x-ui.button>

                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                            Save Customer
                        </x-ui.button>

                    </div>

                </x-slot:footer>

            </div>

        </x-ui.modal>

    </div>
</div>
