<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItemStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'inventory_location_id',
        'quantity_on_hand',
        'quantity_reserved',
        'reorder_level',
        'reorder_quantity',
        'notes',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function inventoryLocation()
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    public static function unassignedForItem(int $inventoryItemId, float $openingQuantity = 0): self
    {
        return static::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->whereNull('inventory_location_id')
            ->firstOrCreate([
                'inventory_item_id' => $inventoryItemId,
                'inventory_location_id' => null,
            ], [
                'quantity_on_hand' => $openingQuantity,
            ]);
    }

    public static function receivingBalanceForItem(int $inventoryItemId): self
    {
        $stock = static::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->whereNull('inventory_location_id')
            ->first();

        if ($stock) {
            return $stock;
        }

        $stock = static::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->orderBy('id')
            ->first();

        return $stock ?: static::create([
            'inventory_item_id' => $inventoryItemId,
            'inventory_location_id' => null,
            'quantity_on_hand' => 0,
        ]);
    }
}
