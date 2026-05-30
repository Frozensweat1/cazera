<?php

namespace App\Models;

use App\Models\Concerns\HasBranchModuleAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebsiteEvent extends Model
{
    use HasBranchModuleAccess, HasFactory;

    protected $fillable = [
        'branch_id',
        'title',
        'slug',
        'tag',
        'date_label',
        'starts_at',
        'ends_at',
        'description',
        'body',
        'image',
        'is_featured',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $event) {
            if (blank($event->slug) && filled($event->title)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
