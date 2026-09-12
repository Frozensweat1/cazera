<?php

namespace Tests\Unit;

use App\Support\SaleRefundSettlement;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SaleRefundSettlementTest extends TestCase
{
    public function test_partial_refund_reduces_total_and_paid_without_creating_debt(): void
    {
        $settlement = SaleRefundSettlement::calculate(100, 100, 30, 'completed');

        $this->assertFalse($settlement['is_full_refund']);
        $this->assertSame(100.0, $settlement['total']);
        $this->assertSame(70.0, $settlement['paid_amount']);
        $this->assertSame(30.0, $settlement['refunded_amount']);
        $this->assertSame(0.0, $settlement['remaining_balance']);
        $this->assertFalse($settlement['is_debt']);
        $this->assertSame('completed', $settlement['status']);
        $this->assertSame(30.0, $settlement['customer_spend_reduction']);
    }

    public function test_partial_refund_preserves_an_existing_outstanding_balance(): void
    {
        $settlement = SaleRefundSettlement::calculate(100, 70, 20, 'served');

        $this->assertFalse($settlement['is_full_refund']);
        $this->assertSame(100.0, $settlement['total']);
        $this->assertSame(50.0, $settlement['paid_amount']);
        $this->assertSame(20.0, $settlement['refunded_amount']);
        $this->assertSame(30.0, $settlement['remaining_balance']);
        $this->assertTrue($settlement['is_debt']);
        $this->assertSame('served', $settlement['status']);
    }

    public function test_refunding_all_paid_value_closes_the_whole_sale(): void
    {
        $settlement = SaleRefundSettlement::calculate(100, 70, 70, 'served');

        $this->assertTrue($settlement['is_full_refund']);
        $this->assertSame(100.0, $settlement['total']);
        $this->assertSame(0.0, $settlement['paid_amount']);
        $this->assertSame(70.0, $settlement['refunded_amount']);
        $this->assertSame(0.0, $settlement['remaining_balance']);
        $this->assertFalse($settlement['is_debt']);
        $this->assertSame('refunded', $settlement['status']);
        $this->assertSame(100.0, $settlement['customer_spend_reduction']);
    }

    public function test_final_refund_only_removes_customer_spend_not_removed_by_earlier_refunds(): void
    {
        $settlement = SaleRefundSettlement::calculate(100, 70, 70, 'completed', 30);

        $this->assertTrue($settlement['is_full_refund']);
        $this->assertSame(100.0, $settlement['refunded_amount']);
        $this->assertSame(70.0, $settlement['customer_spend_reduction']);
    }

    public function test_refund_cannot_exceed_the_paid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SaleRefundSettlement::calculate(100, 50, 50.01, 'completed');
    }
}
