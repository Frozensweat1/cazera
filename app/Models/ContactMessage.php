<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasBranchModuleAccess, HasFactory;

    protected $fillable = [
        'branch_id',
        'module_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'responded_by',
        'responded_at',
        'received_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}
