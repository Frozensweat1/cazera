<div>
    {{-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Maria Skłodowska-Curie --}}
    <div class="space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">

            <div>

                <h1 class="text-2xl font-bold">
                    Categories
                </h1>

                <p class="text-gray-500">
                    Manage product and activity categories
                </p>

            </div>

            <x-ui.button icon="plus" wire:click="create">
                Add Category
            </x-ui.button>

        </div>

        <!-- TABLE -->
        <div class="panel">

            <div class="mb-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.input name="search" wire:model.live.debounce.300ms="search" placeholder="Search categories..." />

                    <x-ui.select name="filterBranch" wire:model.live="filterBranch">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.select name="filterModule" wire:model.live="filterModule">
                        <option value="">All Modules</option>
                        @foreach ($filterModules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

            </div>

            <x-ui.table>

                <thead>

                    <tr>

                        <th>Category</th>

                        <th>Branch</th>

                        <th>Module</th>

                        <th>Sort</th>

                        <th>Status</th>

                        <th class="text-center">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse ($categories as $category)
                        <tr>

                            <td>

                                <div class="flex items-center gap-3">

                                    @if ($category->image_url)
                                        <img src="{{ \Illuminate\Support\Str::startsWith($category->image_url, ['http://', 'https://']) ? $category->image_url : asset('storage/' . $category->image_url) }}"
                                            alt="{{ $category->name }}"
                                            class="w-12 h-12 rounded-lg object-cover border">
                                    @endif

                                    <div>

                                        <p class="font-semibold">
                                            {{ $category->name }}
                                        </p>

                                        <p class="text-xs text-gray-500">
                                            {{ $category->slug }}
                                        </p>

                                    </div>

                                </div>

                            </td>

                            <td>
                                {{ $category->branch?->name }}
                            </td>

                            <td>
                                <span @class([
                                    'badge',
                                    'bg-primary' => $category->module?->type === 'pos',
                                    'bg-warning' => $category->module?->type !== 'pos',
                                ])>
                                    {{ $category->module?->name ?? 'No Module' }}
                                </span>
                            </td>

                            <td>
                                {{ $category->sort_order }}
                            </td>

                            <td>

                                @if ($category->is_active)
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Inactive
                                    </span>
                                @endif

                            </td>

                            <td class="text-center">

                                <x-ui.table-dropdown>

                                    <x-ui.table-dropdown-item icon="pencil-square"
                                        wire:click="edit({{ $category->id }})">
                                        Edit
                                    </x-ui.table-dropdown-item>

                                    <x-ui.table-dropdown-item danger icon="trash"
                                        wire:click="delete({{ $category->id }})">
                                        Delete
                                    </x-ui.table-dropdown-item>

                                </x-ui.table-dropdown>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center py-10 text-gray-500">
                                No categories found.
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </x-ui.table>

            <div class="mt-5">

                {{ $categories->links() }}

            </div>

        </div>

        <!-- MODAL -->
        <x-ui.modal name="category-form" maxWidth="2xl">

            <x-slot:title>

                {{ $categoryId ? 'Edit Category' : 'Create Category' }}

            </x-slot:title>

            <div class="space-y-5">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <x-ui.select label="Branch" name="branch_id" wire:model.live="branch_id">

                        <option value="">
                            Select Branch
                        </option>

                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branch->id == $branch_id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                    <x-ui.select label="Module" name="module_id" wire:model="module_id" :disabled="! $branch_id">

                        <option value="">
                            {{ $branch_id ? 'Select Module' : 'Select branch first' }}
                        </option>

                        @foreach ($formModules as $module)
                            <option value="{{ $module->id }}">
                                {{ $module->name }}
                            </option>
                        @endforeach

                    </x-ui.select>

                    <x-ui.input label="Category Name" name="name" wire:model.live="name" />

                    <x-ui.input label="Slug" name="slug" wire:model="slug" />

                    <x-ui.input label="Sort Order" type="number" name="sort_order" wire:model="sort_order" />

                </div>

                <x-ui.textarea label="Description" name="description" wire:model="description" />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.input label="Image URL" name="image_url" wire:model="image_url"
                        placeholder="https://example.com/category.jpg" />

                    <div>
                        <label class="form-label mb-2" for="image_upload">Upload Image</label>
                        <input id="image_upload" type="file" wire:model="image_upload" accept="image/*"
                            class="form-input w-full">
                        @error('image_upload')
                            <span class="text-danger text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                @if ($image_upload || $image_url)
                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <div class="h-16 w-16 overflow-hidden rounded-lg border bg-white">
                            @if ($image_upload)
                                <img src="{{ $image_upload->temporaryUrl() }}" alt="Category image preview"
                                    class="h-full w-full object-cover">
                            @elseif ($image_url)
                                <img src="{{ \Illuminate\Support\Str::startsWith($image_url, ['http://', 'https://']) ? $image_url : asset('storage/' . $image_url) }}"
                                    alt="Category image preview" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <p class="text-sm text-gray-500">Upload a file or provide an image URL. Uploaded files take priority when saving.</p>
                    </div>
                @endif

                <x-ui.checkbox label="Active Category" name="is_active" wire:model="is_active" />

                <x-slot:footer>

                    <div class="flex justify-end gap-3">

                        <x-ui.button type="button" variant="outline-danger"
                            x-on:click="$dispatch('close-modal', 'category-form')">
                            Cancel
                        </x-ui.button>

                        <x-ui.button wire:click="save" target="save" loadingText="Saving..." icon="check">
                            Save Category
                        </x-ui.button>

                    </div>

                </x-slot:footer>

            </div>

        </x-ui.modal>

    </div>
</div>
