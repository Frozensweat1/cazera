<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\Sale;
use PHPUnit\Framework\TestCase;

class SaleModuleNamesTest extends TestCase
{
    public function test_sale_lists_each_unique_module_name(): void
    {
        $sale = new Sale;
        $sale->setRelation('modules', collect([
            new Module(['name' => 'Restaurant']),
            new Module(['name' => 'Bar']),
            new Module(['name' => 'Restaurant']),
        ]));

        $this->assertSame('Restaurant, Bar', $sale->module_names);
    }

    public function test_sale_has_a_clear_fallback_when_it_has_no_modules(): void
    {
        $sale = new Sale;
        $sale->setRelation('modules', collect());

        $this->assertSame('No module', $sale->module_names);
    }
}
