<?php

namespace App\Livewire\Backoffice\Logs;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Module;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterUser = '';
    public string $filterBranch = '';
    public string $filterModule = '';
    public string $filterEvent = '';
    public string $filterModel = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->subDays(6)->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updated($property): void
    {
        if (str_starts_with($property, 'filter') || in_array($property, ['search', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterUser', 'filterBranch', 'filterModule', 'filterEvent', 'filterModel']);
        $this->dateFrom = now()->subDays(6)->toDateString();
        $this->dateTo = now()->toDateString();
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->with(['user', 'branch', 'module'])
            ->when($this->filterUser, fn ($query) => $query->where('user_id', $this->filterUser))
            ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
            ->when($this->filterModule, fn ($query) => $query->where('module_id', $this->filterModule))
            ->when($this->filterEvent, fn ($query) => $query->where('event', $this->filterEvent))
            ->when($this->filterModel, fn ($query) => $query->where('auditable_type', $this->filterModel))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('logged_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('logged_at', '<=', $this->dateTo))
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('auditable_label', 'like', "%{$this->search}%")
                        ->orWhere('auditable_type', 'like', "%{$this->search}%")
                        ->orWhere('event', 'like', "%{$this->search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%"));
                });
            })
            ->latest('logged_at')
            ->paginate(12);

        return view('livewire.backoffice.logs.audit-logs', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'modules' => Module::query()
                ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
                ->orderBy('name')
                ->get(['id', 'name']),
            'events' => AuditLog::query()->select('event')->distinct()->orderBy('event')->pluck('event'),
            'models' => AuditLog::query()->select('auditable_type')->distinct()->orderBy('auditable_type')->pluck('auditable_type'),
        ]);
    }
}
