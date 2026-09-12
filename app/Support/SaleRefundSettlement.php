<?php

namespace App\Support;

use InvalidArgumentException;

final class SaleRefundSettlement
{
    public static function calculate(
        float $total,
        float $paid,
        float $refundAmount,
        string $currentStatus,
        float $alreadyRefunded = 0,
    ): array {
        $total = round(max(0, $total), 2);
        $paid = round(max(0, $paid), 2);
        $refundAmount = round($refundAmount, 2);
        $alreadyRefunded = round(max(0, $alreadyRefunded), 2);

        if ($refundAmount <= 0 || $refundAmount > $paid) {
            throw new InvalidArgumentException('Refund amount must be greater than zero and cannot exceed the paid amount.');
        }

        $isFullRefund = abs($paid - $refundAmount) < 0.01;

        if ($isFullRefund) {
            return [
                'is_full_refund' => true,
                'total' => $total,
                'paid_amount' => 0.0,
                'refunded_amount' => round($alreadyRefunded + $refundAmount, 2),
                'remaining_balance' => 0.0,
                'is_debt' => false,
                'status' => 'refunded',
                'customer_spend_reduction' => round(max(0, $total - $alreadyRefunded), 2),
            ];
        }

        $adjustedPaid = round(max(0, $paid - $refundAmount), 2);
        $refunded = round($alreadyRefunded + $refundAmount, 2);
        $remaining = round(max(0, $total - $adjustedPaid - $refunded), 2);

        return [
            'is_full_refund' => false,
            'total' => $total,
            'paid_amount' => $adjustedPaid,
            'refunded_amount' => $refunded,
            'remaining_balance' => $remaining,
            'is_debt' => $remaining > 0,
            'status' => $currentStatus,
            'customer_spend_reduction' => $refundAmount,
        ];
    }
}
