<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'slug',
        'type',
        'description',
        'is_active',
        'pos_settings',
        'activity_settings',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pos_settings' => 'array',
        'activity_settings' => 'array',
        'settings' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function staffAssignments()
    {
        return $this->hasMany(ModuleStaff::class);
    }

    public function staff()
    {
        return $this->belongsToMany(
            User::class,
            'module_staff'
        )->withPivot([
            'assigned_at',
            'assigned_by',
            'is_active',
        ])->withTimestamps();
    }
}
