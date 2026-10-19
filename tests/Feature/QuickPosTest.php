<?php

namespace Tests\Feature;

use App\Livewire\Backoffice\Pos\QuickIndex;
use Tests\TestCase;

class QuickPosTest extends TestCase
{
    public function test_quick_pos_route_is_defined(): void
    {
        $this->assertTrue(\Route::has('backoffice.pos.quick'));
        $this->assertSame(url('/backoffice/pos/quick'), route('backoffice.pos.quick'));
    }

    public function test_quick_pos_cart_manipulations(): void
    {
        $component = new QuickIndex();
        $component->cart = [
            [
                'menu_item_id' => 10,
                'module_id' => 2,
                'module_name' => 'Bar',
                'item_name' => 'Cocktail',
                'qty' => 2,
                'unit_price' => 50.0,
                'tax' => 0,
                'discount' => 0,
                'subtotal' => 100.0,
            ],
        ];

        $component->clearCart();
        $this->assertEmpty($component->cart);
    }

    public function test_quick_pos_category_selection(): void
    {
        $component = new QuickIndex();
        $this->assertNull($component->selectedCategoryId);

        $component->selectCategory(5);
        $this->assertSame(5, $component->selectedCategoryId);

        $component->selectCategory(null);
        $this->assertNull($component->selectedCategoryId);
    }
}
