<?php

namespace Tests\Unit;

use App\Models\Sale;
use PHPUnit\Framework\TestCase;

class SaleModuleBreakdownTest extends TestCase
{
    public function test_module_breakdown_allocates_amounts_from_sale_item_subtotals(): void
    {
        $sale = new Sale();

        $items = collect([
            (object) ['module_id' => 5, 'subtotal' => 100],
            (object) ['module_id' => 7, 'subtotal' => 50],
        ]);

        $allocations = $sale->moduleBreakdownForAmount(30, $items);

        $this->assertSame([5 => 20.0, 7 => 10.0], $allocations->toArray());
    }
}
