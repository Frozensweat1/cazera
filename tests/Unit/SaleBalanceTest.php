<?php

namespace Tests\Unit;

use App\Models\Sale;
use PHPUnit\Framework\TestCase;

class SaleBalanceTest extends TestCase
{
    public function test_remaining_balance_accounts_for_collections_and_refunds(): void
    {
        $sale = new Sale([
            'total' => 100,
            'paid_amount' => 50,
            'refunded_amount' => 20,
        ]);

        $this->assertSame(30.0, $sale->remaining);

        $sale->paid_amount = 80;

        $this->assertSame(0.0, $sale->remaining);
    }
}
