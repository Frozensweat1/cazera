<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Customer extends Model
{
    use HasFactory;
    use HasBranchModuleAccess;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'phone',
        'address',
        'latitude',
        'longitude',
        'customer_type',
        'loyalty_points',
        'total_orders',
        'total_spent',
        'total_debt',
        'status',
        'last_order_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'total_spent' => 'decimal:2',
        'total_debt' => 'decimal:2',
        'last_order_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Sale::class);
    }
}
