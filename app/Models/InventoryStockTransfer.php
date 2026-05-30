<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStockTransfer extends Model
{
    use HasBranchModuleAccess, HasFactory;

    protected $fillable = [
        'branch_id',
        'module_id',
        'inventory_item_id',
        'from_branch_id',
        'to_branch_id',
        'destination_module_id',
        'destination_category_id',
        'destination_supplier_id',
        'performed_by',
        'quantity',
        'reference_no',
        'reason',
        'notes',
        'status',
        'transfer_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function destinationModule()
    {
        return $this->belongsTo(Module::class, 'destination_module_id');
    }

    public function destinationCategory()
    {
        return $this->belongsTo(InventoryCategory::class, 'destination_category_id');
    }

    public function destinationSupplier()
    {
        return $this->belongsTo(Supplier::class, 'destination_supplier_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
