<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CareerOpening extends Model
{
    use HasBranchModuleAccess, HasFactory;

    protected $fillable = [
        'branch_id',
        'role',
        'slug',
        'location',
        'employment_type',
        'summary',
        'description',
        'requirements',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requirements' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $opening) {
            if (blank($opening->slug) && filled($opening->role)) {
                $opening->slug = Str::slug($opening->role);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
