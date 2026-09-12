<?php

namespace App\Support;

use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Support\Collection;
use LogicException;

final class SalePaymentRecorder
{
    public static function recordCollection(
        Sale $sale,
        string $method,
        float $amount,
        ?string $reference,
        int $userId,
        string $registerName,
        ?string $notes = null,
    ): Collection {
        return self::record(
            sale: $sale,
            method: $method,
            amount: $amount,
            reference: $reference,
            userId: $userId,
            registerName: $registerName,
            transactionType: 'sale',
            paymentStatus: 'completed',
            notes: $notes,
        );
    }

    public static function recordRefund(
        Sale $sale,
        string $method,
        float $amount,
        int $userId,
        string $registerName,
        ?string $notes = null,
    ): Collection {
        return self::record(
            sale: $sale,
            method: $method,
            amount: $amount,
            reference: null,
            userId: $userId,
            registerName: $registerName,
            transactionType: 'refund',
            paymentStatus: 'refunded',
            notes: $notes,
        );
    }

    private static function record(
        Sale $sale,
        string $method,
        float $amount,
        ?string $reference,
        int $userId,
        string $registerName,
        string $transactionType,
        string $paymentStatus,
        ?string $notes,
    ): Collection {
        $amount = round(abs($amount), 2);

        if ($amount <= 0) {
            throw new LogicException('A payment amount must be greater than zero.');
        }

        $items = $sale->relationLoaded('items')
            ? $sale->items
            : $sale->items()->get(['module_id', 'subtotal']);
        $allocations = $sale->moduleBreakdownForAmount($amount, $items)
            ->filter(fn ($moduleAmount) => (float) $moduleAmount > 0);

        if ($allocations->isEmpty()) {
            throw new LogicException('The sale has no module-based items to allocate this payment to.');
        }

        $transactionAmountSign = $transactionType === 'refund' ? -1 : 1;
        $recordedPayments = collect();

        foreach ($allocations as $moduleId => $moduleAmount) {
            $moduleId = (int) $moduleId;
            $moduleAmount = round((float) $moduleAmount, 2);
            $register = self::openRegister(
                branchId: (int) $sale->branch_id,
                moduleId: $moduleId,
                userId: $userId,
                name: $registerName,
            );
            $signedAmount = $transactionAmountSign * $moduleAmount;

            CashRegisterTransaction::create([
                'cash_register_id' => $register->id,
                'branch_id' => $sale->branch_id,
                'module_id' => $moduleId,
                'sale_id' => $sale->id,
                'performed_by' => $userId,
                'type' => $transactionType,
                'amount' => $signedAmount,
                'notes' => $notes,
                'transaction_date' => now(),
            ]);

            $register->addExpectedBalanceForTransaction($transactionType, $signedAmount);

            $recordedPayments->push(Payment::create([
                'sale_id' => $sale->id,
                'branch_id' => $sale->branch_id,
                'module_id' => $moduleId,
                'cash_register_id' => $register->id,
                'received_by' => $userId,
                'method' => $method,
                'amount' => $moduleAmount,
                'transaction_reference' => $reference,
                'status' => $paymentStatus,
                'notes' => $notes,
                'paid_at' => now(),
            ]));
        }

        return $recordedPayments;
    }

    private static function openRegister(int $branchId, int $moduleId, int $userId, string $name): CashRegister
    {
        $register = CashRegister::query()
            ->where('branch_id', $branchId)
            ->where('module_id', $moduleId)
            ->where('is_open', true)
            ->lockForUpdate()
            ->latest('opened_at')
            ->first();

        return $register ?: CashRegister::create([
            'branch_id' => $branchId,
            'module_id' => $moduleId,
            'opened_by' => $userId,
            'name' => $name,
        ]);
    }
}
