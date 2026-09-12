<?php

namespace Tests\Unit;

use App\Models\Sale;
use PHPUnit\Framework\TestCase;

class SaleModuleBreakdownTest extends TestCase
{
    public function test_module_breakdown_allocates_amounts_from_sale_item_subtotals(): void
    {
        $sale = new Sale;

        $items = collect([
            (object) ['module_id' => 5, 'subtotal' => 100],
            (object) ['module_id' => 7, 'subtotal' => 50],
        ]);

        $allocations = $sale->moduleBreakdownForAmount(30, $items);

        $this->assertSame([5 => 20.0, 7 => 10.0], $allocations->toArray());
    }

    public function test_module_breakdown_keeps_rounding_total_equal_to_payment(): void
    {
        $sale = new Sale;
        $items = collect([
            (object) ['module_id' => 1, 'subtotal' => 10],
            (object) ['module_id' => 2, 'subtotal' => 10],
            (object) ['module_id' => 3, 'subtotal' => 10],
        ]);

        $allocations = $sale->moduleBreakdownForAmount(10, $items);

        $this->assertSame(10.0, round($allocations->sum(), 2));
        $this->assertSame([1 => 3.34, 2 => 3.33, 3 => 3.33], $allocations->toArray());
    }

    public function test_module_breakdown_ignores_items_without_a_module(): void
    {
        $sale = new Sale;

        $allocations = $sale->moduleBreakdownForAmount(10, collect([
            (object) ['module_id' => null, 'subtotal' => 10],
        ]));

        $this->assertTrue($allocations->isEmpty());
    }
}
