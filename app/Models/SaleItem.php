<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasBranchModuleAccess;

    protected $fillable = [
        'sale_id',
        'branch_id',
        'module_id',
        'menu_item_id',
        'item_name',
        'sku',
        'qty',
        'unit_price',
        'tax',
        'discount',
        'subtotal',
        'total',
        'status',
        'is_kitchen_notified',
        'kitchen_status',
        'notes',
        'prepared_at',
        'served_at',
        'kitchen_started_at',
        'kitchen_completed_at',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'prepared_at' => 'datetime',
        'served_at' => 'datetime',
        'is_kitchen_notified' => 'boolean',
        'kitchen_started_at' => 'datetime',
        'kitchen_completed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getLineTotalAttribute(): float
    {
        return $this->qty * $this->unit_price;
    }
}
