<?php

namespace Tests\Feature;

use App\Support\PosCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PosCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_module_access_invalidation_rotates_the_cache_key(): void
    {
        $before = PosCache::accessibleModulesKey(7, 3);
        $otherBranchBefore = PosCache::accessibleModulesKey(7, 4);

        PosCache::invalidateModuleAccess(3);

        $this->assertNotSame($before, PosCache::accessibleModulesKey(7, 3));
        $this->assertSame($otherBranchBefore, PosCache::accessibleModulesKey(7, 4));
    }

    public function test_branch_configuration_invalidation_forgets_cached_values(): void
    {
        Cache::put(PosCache::taxesKey(3), ['tax'], 60);
        Cache::put(PosCache::discountsKey(3), ['discount'], 60);
        Cache::put(PosCache::receiptSettingsKey(), ['business_name' => 'Old'], 60);

        PosCache::invalidateTaxes(3);
        PosCache::invalidateDiscounts(3);
        PosCache::invalidateReceiptSettings();

        $this->assertFalse(Cache::has(PosCache::taxesKey(3)));
        $this->assertFalse(Cache::has(PosCache::discountsKey(3)));
        $this->assertFalse(Cache::has(PosCache::receiptSettingsKey()));
    }
}
