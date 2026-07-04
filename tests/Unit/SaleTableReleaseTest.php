<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Support\SaleTableRelease;
use PHPUnit\Framework\TestCase;

class SaleTableReleaseTest extends TestCase
{
    public function test_fully_paid_table_sale_can_release(): void
    {
        $sale = new Sale([
            'table_id' => 10,
            'remaining_balance' => 0,
            'status' => 'served',
        ]);

        $this->assertTrue(SaleTableRelease::canRelease($sale));
    }

    public function test_completed_table_sale_can_release(): void
    {
        $sale = new Sale([
            'table_id' => 10,
            'remaining_balance' => 25,
            'status' => 'completed',
        ]);

        $this->assertTrue(SaleTableRelease::canRelease($sale));
    }

    public function test_unsettled_table_sale_cannot_release(): void
    {
        $sale = new Sale([
            'table_id' => 10,
            'remaining_balance' => 25,
            'status' => 'served',
        ]);

        $this->assertFalse(SaleTableRelease::canRelease($sale));
    }

    public function test_sale_without_table_cannot_release(): void
    {
        $sale = new Sale([
            'table_id' => null,
            'remaining_balance' => 0,
            'status' => 'completed',
        ]);

        $this->assertFalse(SaleTableRelease::canRelease($sale));
    }
}
