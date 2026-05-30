<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionDayLock extends Model
{
    use HasFactory;
    use HasBranchModuleAccess;

    protected $fillable = [
        'branch_id',
        'module_id',
        'production_date',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'production_date' => 'date',
        'locked_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function locker()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
