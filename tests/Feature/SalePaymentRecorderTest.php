<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\SalePaymentRecorder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SalePaymentRecorderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('sale_number');
            $table->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('module_id');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('opened_by');
            $table->string('name');
            $table->decimal('expected_balance', 12, 2)->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();
        });
        Schema::create('cash_register_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_register_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('performed_by');
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('cash_register_id');
            $table->unsignedBigInteger('received_by');
            $table->string('method');
            $table->decimal('amount', 12, 2);
            $table->string('transaction_reference')->nullable();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_collection_and_refund_are_posted_to_each_sale_item_module(): void
    {
        $sale = Sale::create(['branch_id' => 1, 'sale_number' => 'TEST-001']);
        SaleItem::create(['sale_id' => $sale->id, 'module_id' => 5, 'subtotal' => 100]);
        SaleItem::create(['sale_id' => $sale->id, 'module_id' => 7, 'subtotal' => 50]);

        DB::transaction(function () use ($sale) {
            SalePaymentRecorder::recordCollection(
                sale: $sale,
                method: 'cash',
                amount: 30,
                reference: 'PAY-001',
                userId: 9,
                registerName: 'Test register',
            );
            SalePaymentRecorder::recordRefund(
                sale: $sale,
                method: 'cash',
                amount: 15,
                userId: 9,
                registerName: 'Test register',
            );
        });

        $this->assertSame([5 => 20.0, 7 => 10.0], Payment::where('status', 'completed')->get()->mapWithKeys(
            fn (Payment $payment) => [$payment->module_id => (float) $payment->amount]
        )->all());
        $this->assertSame([5 => 10.0, 7 => 5.0], Payment::where('status', 'refunded')->get()->mapWithKeys(
            fn (Payment $payment) => [$payment->module_id => (float) $payment->amount]
        )->all());
        $this->assertSame([5 => 10.0, 7 => 5.0], CashRegister::get()->mapWithKeys(
            fn (CashRegister $register) => [$register->module_id => (float) $register->expected_balance]
        )->all());
    }
}
