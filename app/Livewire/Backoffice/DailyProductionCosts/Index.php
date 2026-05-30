<?php

namespace App\Livewire\Backoffice\DailyProductionCosts;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\DailyProductionCost;
use App\Models\ProductionDayLock;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterDate = '';

    public $lockBranch = '';
    public $lockModule = '';
    public $lockProductionDate = '';

    public $costId;
    public $branch_id;
    public $module_id;
    public $production_date;
    public $title;
    public $amount = 0;
    public $notes;
    public $is_locked = false;

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'module_id' => 'required|exists:modules,id',
            'production_date' => 'required|date',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_locked' => 'boolean',
        ];
    }

    public function render()
    {
        $lock = null;
        $canManageLocks = $this->canManageLocks();

        if ($this->lockBranch && $this->lockModule && $this->lockProductionDate) {
            $lock = ProductionDayLock::where('branch_id', $this->lockBranch)
                ->where('module_id', $this->lockModule)
                ->where('production_date', $this->lockProductionDate)
                ->first();
        }

        return view('livewire.backoffice.daily-production-costs.index', [
            'dailyProductionCosts' => DailyProductionCost::with(['branch', 'module', 'recorder', 'locker'])
                ->accessible()
                ->when($this->search, fn($query) => $query->where('title', 'like', "%{$this->search}%"))
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterDate, fn($query) => $query->where('production_date', $this->filterDate))
                ->latest()
                ->paginate(10),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $this->branch_id ?: null),
            'productionDayLock' => $lock,
            'canManageLocks' => $canManageLocks,
        ]);
    }

    public function updatedFilterBranch()
    {
        $this->filterModule = '';
    }

    public function updatedBranchId()
    {
        $this->module_id = '';
    }

    public function updatedLockBranch()
    {
        $this->lockModule = '';
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'daily-production-cost-form');
    }

    public function edit($id)
    {
        $cost = DailyProductionCost::accessible()->findOrFail($id);

        $this->costId = $cost->id;
        $this->branch_id = $cost->branch_id;
        $this->module_id = $cost->module_id;
        $this->production_date = $cost->production_date->toDateString();
        $this->title = $cost->title;
        $this->amount = $cost->amount;
        $this->notes = $cost->notes;
        $this->is_locked = $cost->is_locked;

        $this->dispatch('open-modal', 'daily-production-cost-form');
    }

    public function save()
    {
        $this->validate();

        $dayLocked = ProductionDayLock::where('branch_id', $this->branch_id)
            ->where('module_id', $this->module_id)
            ->where('production_date', $this->production_date)
            ->exists();

        if ($dayLocked && ! $this->costId) {
            LivewireAlert::title('Production Day Locked')
                ->text('Cannot create a production cost for a locked production day.')
                ->warning()
                ->show();

            return;
        }

        if ($this->costId) {
            $cost = DailyProductionCost::accessible()->findOrFail($this->costId);

            if ($cost->is_locked) {
                LivewireAlert::title('Entry Locked')
                    ->text('This production cost entry is locked and cannot be modified.')
                    ->warning()
                    ->show();

                return;
            }

            $cost->update([
                'branch_id' => $this->branch_id,
                'module_id' => $this->module_id,
                'production_date' => $this->production_date,
                'title' => $this->title,
                'amount' => $this->amount,
                'notes' => $this->notes,
                'is_locked' => $this->canManageLocks() ? $this->is_locked : $cost->is_locked,
            ]);
        } else {
            DailyProductionCost::create([
                'branch_id' => $this->branch_id,
                'module_id' => $this->module_id,
                'recorded_by' => auth()->id(),
                'production_date' => $this->production_date,
                'title' => $this->title,
                'amount' => $this->amount,
                'notes' => $this->notes,
                'is_locked' => $this->canManageLocks() ? $this->is_locked : false,
            ]);
        }

        $this->resetForm();
        $this->dispatch('close-modal', 'daily-production-cost-form');

        LivewireAlert::title('Production Cost Saved')
            ->text('Daily production cost entry has been saved successfully.')
            ->success()
            ->show();
    }

    public function toggleProductionDayLock()
    {
        abort_unless($this->canManageLocks(), 403);

        $this->validate([
            'lockBranch' => 'required|exists:branches,id',
            'lockModule' => 'required|exists:modules,id',
            'lockProductionDate' => 'required|date',
        ]);

        $lock = ProductionDayLock::where('branch_id', $this->lockBranch)
            ->where('module_id', $this->lockModule)
            ->where('production_date', $this->lockProductionDate)
            ->first();

        if ($lock) {
            $lock->delete();

            LivewireAlert::title('Production Day Unlocked')
                ->text('The selected production day has been unlocked.')
                ->success()
                ->show();
        } else {
            ProductionDayLock::create([
                'branch_id' => $this->lockBranch,
                'module_id' => $this->lockModule,
                'production_date' => $this->lockProductionDate,
                'locked_by' => auth()->id(),
                'locked_at' => now(),
            ]);

            LivewireAlert::title('Production Day Locked')
                ->text('The selected production day has been locked.')
                ->success()
                ->show();
        }
    }

    public function lockCost($id)
    {
        abort_unless($this->canManageLocks(), 403);

        $cost = DailyProductionCost::accessible()->findOrFail($id);

        $cost->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ]);

        LivewireAlert::title('Entry Locked')
            ->text('The production cost entry has been locked.')
            ->success()
            ->show();
    }

    public function unlockCost($id)
    {
        abort_unless($this->canManageLocks(), 403);

        $cost = DailyProductionCost::accessible()->findOrFail($id);

        $cost->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        LivewireAlert::title('Entry Unlocked')
            ->text('The production cost entry has been unlocked.')
            ->success()
            ->show();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('open-modal', 'delete-production-cost-' . $id);
    }

    public function delete($id)
    {
        $cost = DailyProductionCost::accessible()->findOrFail($id);

        if ($cost->is_locked) {
            LivewireAlert::title('Entry Locked')
                ->text('Cannot delete a locked production cost entry.')
                ->warning()
                ->show();

            return;
        }

        $cost->delete();

        $this->dispatch('close-modal', 'delete-production-cost-' . $id);

        LivewireAlert::title('Deleted')
            ->text('Production cost entry deleted successfully.')
            ->success()
            ->show();
    }

    public function resetForm()
    {
        $this->reset([
            'costId',
            'module_id',
            'production_date',
            'title',
            'amount',
            'notes',
            'is_locked',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->production_date = now()->toDateString();
        $this->is_locked = false;
    }

    protected function canManageLocks(): bool
    {
        return auth()->user()?->hasAnyRole(['Super Admin', 'Branch Manager']) ?? false;
    }
}
