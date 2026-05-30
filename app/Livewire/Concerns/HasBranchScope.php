<?php

namespace App\Livewire\Concerns;

use App\Models\Branch;
use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasBranchScope
{
    public function initializeHasBranchScope(): void
    {
        $this->ensureAuthorizedBranchContext();

        $shouldDefaultFilterBranch = ! auth()->user()?->isSuperAdmin();

        if ($shouldDefaultFilterBranch && property_exists($this, 'filterBranch') && empty($this->filterBranch)) {
            $this->filterBranch = session('branch_id') ?: '';
        }

        if (property_exists($this, 'branch_id') && empty($this->branch_id)) {
            $this->branch_id = session('branch_id') ?: '';
        }

        if ($shouldDefaultFilterBranch && property_exists($this, 'filterBranch') && empty($this->filterBranch) && session('branch_id')) {
            $this->filterBranch = session('branch_id');
        }

        if (property_exists($this, 'branch_id') && empty($this->branch_id) && session('branch_id')) {
            $this->branch_id = session('branch_id');
        }
    }

    public function getCurrentBranchProperty(): ?Branch
    {
        if (! session('branch_id') || ! auth()->user()?->canAccessBranch(session('branch_id'))) {
            return null;
        }

        return Branch::find(session('branch_id'));
    }

    protected function ensureAuthorizedBranchContext(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $branchId = session('branch_id');

        if ($branchId && $user->canAccessBranch($branchId)) {
            return;
        }

        $branch = $user->accessibleBranches()->first();

        if ($branch) {
            session(['branch_id' => $branch->id]);
        }
    }

    protected function accessibleBranches(): Collection
    {
        $user = auth()->user();

        return $user
            ? $user->accessibleBranches()->get()
            : Branch::query()->where('is_active', true)->orderBy('id')->get();
    }

    protected function accessibleModules(?int $branchId = null, ?string $type = null): Collection
    {
        $user = auth()->user();

        return $user
            ? $user->accessibleModules($branchId, $type)->get()
            : Module::query()
                ->where('is_active', true)
                ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
                ->when($type, fn (Builder $query) => $query->where('type', $type))
                ->orderBy('name')
                ->get();
    }

    protected function authorizeBranch(mixed $branchId): void
    {
        abort_unless(auth()->user()?->canAccessBranch($branchId), 403);
    }

    protected function authorizeModule(mixed $moduleId, mixed $branchId = null): void
    {
        abort_unless(auth()->user()?->canAccessModule($moduleId, $branchId), 403);
    }
}
