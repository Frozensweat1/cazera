<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class PosCache
{
    public static function accessibleModulesKey(int $userId, int $branchId): string
    {
        return "pos.accessible_modules.{$userId}.{$branchId}.".self::moduleAccessVersion($branchId);
    }

    public static function taxesKey(int $branchId): string
    {
        return "pos.branch_taxes.{$branchId}";
    }

    public static function discountsKey(int $branchId): string
    {
        return "pos.branch_discounts.{$branchId}";
    }

    public static function receiptSettingsKey(): string
    {
        return 'pos.receipt_settings';
    }

    public static function invalidateModuleAccess(?int $branchId): void
    {
        if ($branchId) {
            Cache::forever(self::moduleAccessVersionKey($branchId), self::moduleAccessVersion($branchId) + 1);
        }
    }

    public static function invalidateTaxes(?int $branchId): void
    {
        if ($branchId) {
            Cache::forget(self::taxesKey($branchId));
        }
    }

    public static function invalidateDiscounts(?int $branchId): void
    {
        if ($branchId) {
            Cache::forget(self::discountsKey($branchId));
        }
    }

    public static function invalidateReceiptSettings(): void
    {
        Cache::forget(self::receiptSettingsKey());
    }

    private static function moduleAccessVersion(int $branchId): int
    {
        return (int) Cache::get(self::moduleAccessVersionKey($branchId), 1);
    }

    private static function moduleAccessVersionKey(int $branchId): string
    {
        return "pos.module_access_version.{$branchId}";
    }
}
