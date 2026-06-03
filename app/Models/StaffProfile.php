<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    use HasBranchModuleAccess;

    protected $fillable = [
        'user_id',
        'branch_id',
        'module_id',
        'employee_code',
        'job_title',
        'department',
        'employment_type',
        'employment_status',
        'hire_date',
        'date_of_birth',
        'gender',
        'national_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'address',
        'notes',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'date_of_birth' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
