<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    use HasBranchModuleAccess;

    protected $fillable = [
        'branch_id',
        'module_id',
        'expense_category_id',
        'recorded_by',
        'expense_date',
        'title',
        'amount',
        'notes',
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'is_locked' => 'boolean',
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

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function locker()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
