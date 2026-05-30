<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasBranchModuleAccess
{
    public function scopeAccessible(Builder $query, ?User $user = null): Builder
    {
        $user ??= auth()->user();

        if (! $user || $user->isSuperAdmin()) {
            return $query;
        }

        $branchIds = $user->accessibleBranches()->pluck('branches.id');

        $query->whereIn($query->qualifyColumn('branch_id'), $branchIds);

        if ($user->isBranchManager() || ! $this->hasModuleColumn()) {
            return $query;
        }

        return $query->whereIn($query->qualifyColumn('module_id'), $user->accessibleModules()->pluck('modules.id'));
    }

    public function scopeForBranch(Builder $query, mixed $branchId): Builder
    {
        return $branchId ? $query->where($query->qualifyColumn('branch_id'), $branchId) : $query;
    }

    public function scopeForModule(Builder $query, mixed $moduleId): Builder
    {
        return $moduleId && $this->hasModuleColumn()
            ? $query->where($query->qualifyColumn('module_id'), $moduleId)
            : $query;
    }

    protected function hasModuleColumn(): bool
    {
        return in_array('module_id', $this->getFillable(), true)
            || array_key_exists('module_id', $this->getCasts());
    }
}
