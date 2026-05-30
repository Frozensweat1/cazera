<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'phone',
        'email',
        'latitude',
        'longitude',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function staff()
    {
        return $this->belongsToMany(User::class, 'branch_staff')
            ->withPivot(['assigned_at', 'assigned_by', 'is_active'])
            ->withTimestamps();
    }

}
