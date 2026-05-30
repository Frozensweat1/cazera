<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerApplication extends Model
{
    use HasBranchModuleAccess, HasFactory;

    protected $fillable = [
        'career_opening_id',
        'branch_id',
        'role',
        'name',
        'email',
        'phone',
        'message',
        'status',
        'reviewed_by',
        'reviewed_at',
        'submitted_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function opening()
    {
        return $this->belongsTo(CareerOpening::class, 'career_opening_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
