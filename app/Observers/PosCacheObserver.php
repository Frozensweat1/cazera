<?php

namespace App\Observers;

use App\Models\BranchStaff;
use App\Models\Discount;
use App\Models\Module;
use App\Models\ModuleStaff;
use App\Models\Tax;
use App\Models\WebsiteSetting;
use App\Support\PosCache;
use Illuminate\Database\Eloquent\Model;

final class PosCacheObserver
{
    public function saved(Model $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model);
    }

    public function restored(Model $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(Model $model): void
    {
        $branchId = (int) $model->getAttribute('branch_id');
        $originalBranchId = (int) $model->getOriginal('branch_id');

        match (true) {
            $model instanceof Tax => PosCache::invalidateTaxes($branchId),
            $model instanceof Discount => PosCache::invalidateDiscounts($branchId),
            $model instanceof WebsiteSetting => PosCache::invalidateReceiptSettings(),
            $model instanceof Module,
            $model instanceof ModuleStaff,
            $model instanceof BranchStaff => PosCache::invalidateModuleAccess($branchId),
            default => null,
        };

        if ($originalBranchId && $originalBranchId !== $branchId) {
            match (true) {
                $model instanceof Tax => PosCache::invalidateTaxes($originalBranchId),
                $model instanceof Discount => PosCache::invalidateDiscounts($originalBranchId),
                $model instanceof Module,
                $model instanceof ModuleStaff,
                $model instanceof BranchStaff => PosCache::invalidateModuleAccess($originalBranchId),
                default => null,
            };
        }
    }
}
