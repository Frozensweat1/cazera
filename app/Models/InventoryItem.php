<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;
    use HasBranchModuleAccess;

    protected $fillable = [
        'branch_id',
        'module_id',
        'supplier_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'unit_cost',
        'unit_price',
        'quantity_on_hand',
        'reorder_level',
        'reorder_quantity',
        'is_trackable',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'quantity_on_hand' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
        'is_trackable' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function stocks()
    {
        return $this->hasMany(InventoryItemStock::class);
    }

    public function adjustments()
    {
        return $this->hasMany(InventoryStockAdjustment::class);
    }

    public function transfers()
    {
        return $this->hasMany(InventoryStockTransfer::class);
    }

    public function refreshAggregateQuantity(): void
    {
        $this->forceFill([
            'quantity_on_hand' => $this->is_trackable ? $this->stocks()->sum('quantity_on_hand') : 0,
        ])->save();
    }
}
