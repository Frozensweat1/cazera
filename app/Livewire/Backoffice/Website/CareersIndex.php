<?php

namespace App\Livewire\Backoffice\Website;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\CareerApplication;
use App\Models\CareerOpening;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class CareersIndex extends Component
{
    use HasBranchScope;
    use WithPagination;

    public string $search = '';
    public string $tab = 'openings';
    public $openingId;
    public $branch_id = '';
    public string $role = '';
    public string $slug = '';
    public string $location = '';
    public string $employment_type = 'Full-time';
    public string $summary = '';
    public string $description = '';
    public string $requirementsText = '';
    public bool $is_active = true;
    public int $sort_order = 0;

    protected function rules(): array
    {
        return [
            'branch_id' => 'nullable|exists:branches,id',
            'role' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:career_openings,slug,' . $this->openingId,
            'location' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:80',
            'summary' => 'nullable|string|max:1000',
            'description' => 'nullable|string',
            'requirementsText' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.website.careers-index', [
            'openings' => CareerOpening::with('branch')
                ->accessible()
                ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                    ->where('role', 'like', "%{$this->search}%")
                    ->orWhere('summary', 'like', "%{$this->search}%")))
                ->orderBy('sort_order')
                ->paginate(10, ['*'], 'openingsPage'),
            'applications' => CareerApplication::with(['opening', 'branch'])
                ->accessible()
                ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('role', 'like', "%{$this->search}%")))
                ->latest('submitted_at')
                ->paginate(10, ['*'], 'applicationsPage'),
            'branches' => $this->accessibleBranches(),
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'career-opening-form');
    }

    public function edit(int $id): void
    {
        $opening = CareerOpening::accessible()->findOrFail($id);
        $this->openingId = $opening->id;
        $this->branch_id = $opening->branch_id ?: '';
        $this->role = $opening->role;
        $this->slug = $opening->slug;
        $this->location = $opening->location ?: '';
        $this->employment_type = $opening->employment_type ?: 'Full-time';
        $this->summary = $opening->summary ?: '';
        $this->description = $opening->description ?: '';
        $this->requirementsText = implode("\n", $opening->requirements ?? []);
        $this->is_active = (bool) $opening->is_active;
        $this->sort_order = (int) $opening->sort_order;
        $this->dispatch('open-modal', 'career-opening-form');
    }

    public function save(): void
    {
        $this->slug = $this->slug ?: Str::slug($this->role);
        $this->validate();

        CareerOpening::updateOrCreate(['id' => $this->openingId], [
            'branch_id' => $this->branch_id ?: null,
            'role' => $this->role,
            'slug' => $this->slug,
            'location' => $this->location ?: null,
            'employment_type' => $this->employment_type ?: null,
            'summary' => $this->summary ?: null,
            'description' => $this->description ?: null,
            'requirements' => collect(preg_split('/\r\n|\r|\n/', $this->requirementsText))->map(fn ($line) => trim($line))->filter()->values()->toArray(),
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ]);

        $this->dispatch('close-modal', 'career-opening-form');
        LivewireAlert::title('Career Opening Saved')->success()->show();
        $this->resetForm();
    }

    public function updateApplicationStatus(int $id, string $status): void
    {
        abort_unless(in_array($status, ['new', 'reviewing', 'shortlisted', 'closed'], true), 422);
        CareerApplication::accessible()->findOrFail($id)->update([
            'status' => $status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        LivewireAlert::title('Application Updated')->success()->show();
    }

    private function resetForm(): void
    {
        $this->reset(['openingId', 'branch_id', 'role', 'slug', 'location', 'summary', 'description', 'requirementsText']);
        $this->employment_type = 'Full-time';
        $this->is_active = true;
        $this->sort_order = 0;
    }
}
