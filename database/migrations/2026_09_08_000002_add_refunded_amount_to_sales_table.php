<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('refunded_amount', 12, 2)->default(0)->after('paid_amount');
        });

        DB::table('payments')
            ->where('status', 'refunded')
            ->selectRaw('sale_id, SUM(amount) as refunded_amount')
            ->groupBy('sale_id')
            ->orderBy('sale_id')
            ->chunkById(500, function ($refunds) {
                foreach ($refunds as $refund) {
                    DB::table('sales')->where('id', $refund->sale_id)->update([
                        'refunded_amount' => $refund->refunded_amount,
                    ]);
                }
            }, 'sale_id', 'sale_id');
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('refunded_amount');
        });
    }
};
