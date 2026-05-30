<div>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
    <div class="space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-2xl font-bold">
                    Menu Items
                </h1>

                <p class="text-gray-500">
                    Manage menu items and activity items
                </p>

            </div>

            <div class="flex items-center gap-3">

                @if (count($selected) > 0)
                    <x-ui.button variant="outline-danger" icon="trash" wire:click="confirmBulkDelete"
                        target="confirmBulkDelete">
                        Delete Selected ({{ count($selected) }})
                    </x-ui.button>
                @endif

                <x-ui.button icon="plus" wire:click="create">
                    Add Menu Item
                </x-ui.button>

            </div>

        </div>

        <!-- FILTERS -->
        <div class="panel">

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search..." />

                <x-ui.select wire:model.live="filterBranch">

                    <option value="">
                        All Branches
                    </option>

                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">
                            {{ $branch->name }}
                        </option>
                    @endforeach

                </x-ui.select>

                <x-ui.select wire:model.live="filterModule">

                    <option value="">
                        All Modules
                    </option>

                    @foreach ($filterModules as $module)
                        <option value="{{ $module->id }}">
                            {{ $module->name }}
                        </option>
                    @endforeach

                </x-ui.select>

                <x-ui.select wire:model.live="filterCategory">

                    <option value="">
                        All Categories
                    </option>

                    @foreach ($filterCategories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                    @endforeach

                </x-ui.select>

                <x-ui.select wire:model.live="filterStatus">

                    <option value="">
                        All Status
                    </option>

                    <option value="available">
                        Available
                    </option>

                    <option value="unavailable">
                        Unavailable
                    </option>

                    <option value="out_of_stock">
                        Out Of Stock
                    </option>

                </x-ui.select>

            </div>

        </div>

        <!-- TABLE -->
        <div class="panel">

            <x-ui.table>

                <thead>

                    <tr>

                        <th width="50">
                            <input type="checkbox" wire:model.live="selectPage">
                        </th>

                        <th>Item</th>

                        <th>Branch</th>

                        <th>Module</th>

                        <th>Category</th>

                        <th>Price</th>

                        <th>Qty</th>

                        <th>Status</th>

                        <th>Trackable</th>

                        <th class="text-center">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse ($menuItems as $item)
                        <tr>

                            <td>

                                <input type="checkbox" value="{{ $item->id }}" wire:model.live="selected">

                            </td>

                            <td>

                                <div class="flex items-center gap-3">

                                    @if ($item->image_url)
                                        <img src="{{ \Illuminate\Support\Str::startsWith($item->image_url, ['http://', 'https://']) ? $item->image_url : asset('storage/' . ltrim(str_replace('/storage/', '', $item->image_url), '/')) }}"
                                            alt="{{ $item->name }}"
                                            class="w-12 h-12 rounded-lg object-cover border">
                                    @endif

                                    <div>

                                        <p class="font-semibold">
                                            {{ $item->name }}
                                        </p>

                                        <p class="text-xs text-gray-500">
                                            {{ $item->slug }}
                                        </p>

                                    </div>

                                </div>

                            </td>

                            <td>
                                {{ $item->branch?->name ?? '-' }}
                            </td>

                            <td>
                                <span @class([
                                    'badge',
                                    'bg-primary' => $item->module?->type === 'pos',
                                    'bg-warning' => $item->module?->type !== 'pos',
                                ])>
                                    {{ $item->module?->name ?? 'No Module' }}
                                </span>
                            </td>

                            <td>
                                {{ $item->category?->name }}
                            </td>

                            <td>
                                {{ number_format($item->price, 2) }}
                            </td>

                            <td>
                                {{ $item->quantity }}
                            </td>

                            <td>

                                @if ($item->status === 'available')
                                    <span class="badge bg-success">
                                        Available
                                    </span>
                                @elseif ($item->status === 'unavailable')
                                    <span class="badge bg-warning">
                                        Unavailable
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Out Of Stock
                                    </span>
                                @endif

                            </td>

                            <td>

                                @if ($item->is_trackable)
                                    <span class="badge bg-primary">
                                        Yes
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        No
                                    </span>
                                @endif

                            </td>

                            <td class="text-center">

                                <x-ui.table-dropdown>

                                    <x-ui.table-dropdown-item icon="pencil-square" wire:click="edit({{ $item->id }})">
                                        Edit
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item danger icon="trash" wire:click="delete({{ $item->id }})">
                                        Delete
                                    </x-ui.table-dropdown-item>

                                </x-ui.table-dropdown>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="10" class="text-center py-10 text-gray-500">
                                No menu items found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </x-ui.table>

            <div class="mt-5">

                {{ $menuItems->links() }}

            </div>

        </div>

        <!-- FORM MODAL -->
        <x-ui.modal name="menu-item-form" maxWidth="4xl">

            <x-slot:title>

                {{ $menuItemId ? 'Edit Menu Item' : 'Create Menu Item' }}

            </x-slot:title>

            <div class="space-y-5">

                <!-- TOP GRID -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">

                        <option value="">
                            Select Branch
                        </option>

                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                    <x-ui.select label="Module" name="module_id" wire:model.live="module_id" :disabled="! $branch_id">

                        <option value="">
                            {{ $branch_id ? 'Select Module' : 'Select branch first' }}
                        </option>

                        @foreach ($formModules as $module)
                            <option value="{{ $module->id }}">
                                {{ $module->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                    <x-ui.select label="Category" name="category_id" wire:model="category_id" :disabled="! $module_id">

                        <option value="">
                            {{ $module_id ? 'Select Category' : 'Select module first' }}
                        </option>

                        @foreach ($formCategories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                </div>

                <!-- NAME -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-ui.input label="Item Name" name="name" wire:model.live="name" />

                    <x-ui.input label="Slug" name="slug" wire:model="slug" />

                </div>

                <!-- DESCRIPTION -->
                <x-ui.textarea label="Description" name="description" wire:model="description" />

                <!-- IMAGE -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Image URL" name="image_url" wire:model="image_url"
                        placeholder="https://example.com/menu-item.jpg" />

                    <div>
                        <label class="form-label mb-2" for="menu-item-image">
                            Upload Image
                        </label>

                        <input id="menu-item-image" type="file" wire:model="image" class="form-input w-full"
                            accept="image/*">

                        @error('image')
                            <span class="text-danger text-sm">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>

                @if ($image || $image_url)
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <div class="h-24 w-24 overflow-hidden rounded-xl border bg-white">
                            @if ($image)
                                <img src="{{ $image->temporaryUrl() }}" alt="Menu item image preview"
                                    class="h-full w-full object-cover">
                            @elseif ($image_url)
                                <img src="{{ \Illuminate\Support\Str::startsWith($image_url, ['http://', 'https://']) ? $image_url : asset('storage/' . ltrim(str_replace('/storage/', '', $image_url), '/')) }}"
                                    alt="Menu item image preview" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">Upload a file or provide an image URL. Uploaded files take priority when saving.</p>
                    </div>
                @endif

                <!-- PRICING -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <x-ui.input label="Quantity" type="number" name="quantity" wire:model="quantity" />

                    <x-ui.input label="Selling Price" type="number" step="0.01" name="price"
                        wire:model="price" />

                    <x-ui.input label="Cost Price" type="number" step="0.01" name="cost_price"
                        wire:model="cost_price" />

                    <x-ui.input label="Preparation Time (mins)" type="number" name="preparation_time"
                        wire:model="preparation_time" />

                </div>

                <!-- STATUS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <x-ui.select label="Status" name="status" wire:model="status">

                        <option value="available">
                            Available
                        </option>

                        <option value="unavailable">
                            Unavailable
                        </option>

                        <option value="out_of_stock">
                            Out Of Stock
                        </option>

                    </x-ui.select>

                    <x-ui.input label="Sort Order" type="number" name="sort_order" wire:model="sort_order" />

                    <div class="flex items-center pt-8">

                        <x-ui.checkbox label="Track Inventory" name="is_trackable" wire:model="is_trackable" />

                    </div>

                </div>

                <x-slot:footer>

                    <div class="flex justify-end gap-3">

                        <x-ui.button type="button" variant="outline-secondary"
                            x-on:click="$dispatch('close-modal', 'menu-item-form')">
                            Cancel
                        </x-ui.button>

                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                            Save Menu Item
                        </x-ui.button>

                    </div>

                </x-slot:footer>

            </div>

        </x-ui.modal>

    </div>
</div>
