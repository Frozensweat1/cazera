<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'eyebrow',
        'subtitle',
        'body',
        'hero_image',
        'sections',
        'meta_title',
        'meta_description',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'sections' => 'array',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];
}
