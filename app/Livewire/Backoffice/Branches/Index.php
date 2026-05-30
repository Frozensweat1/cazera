<?php

namespace App\Livewire\Backoffice\Branches;

use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $branchId;

    public $name;
    public $slug;
    public $location;
    public $phone;
    public $email;
    public $latitude;
    public $longitude;
    public $is_active = true;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:branches,slug,' . $this->branchId,
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.branches.index', [
            'branches' => Branch::query()
                ->when(
                    $this->search,
                    fn($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('location', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                )
                ->latest()
                ->paginate(10),
        ]);
    }

    public function updatedName()
    {
        if (!$this->branchId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'branchId',
            'name',
            'slug',
            'location',
            'phone',
            'email',
            'latitude',
            'longitude',
        ]);

        $this->is_active = true;
    }

    public function create()
    {
        $this->resetForm();

        $this->dispatch('open-modal', 'branch-form');
        $this->dispatch('request-location');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);

        $this->branchId = $branch->id;

        $this->name = $branch->name;
        $this->slug = $branch->slug;
        $this->location = $branch->location;
        $this->phone = $branch->phone;
        $this->email = $branch->email;
        $this->latitude = $branch->latitude;
        $this->longitude = $branch->longitude;
        $this->is_active = $branch->is_active;

        $this->dispatch('open-modal', 'branch-form');
    }

    public function save()
    {
        $this->validate();

        Branch::updateOrCreate(
            ['id' => $this->branchId],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'location' => $this->location,
                'phone' => $this->phone,
                'email' => $this->email,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'is_active' => $this->is_active,
            ]
        );

        $this->dispatch('close-modal', 'branch-form');

        LivewireAlert::title('Branch Saved')
            ->text('The branch has been successfully saved.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        LivewireAlert::title('Delete Branch')
            ->text('Are you sure you want to delete this branch?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data)
    {
        $id = $data['id'];
        Branch::findOrFail($id)->delete();

        LivewireAlert::title('Branch Deleted')
            ->text('The branch has been deleted successfully.')
            ->success()
            ->show();
    }
}
