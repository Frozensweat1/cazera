<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'tagline',
        'email',
        'phone',
        'whatsapp',
        'address',
        'google_map_url',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'tiktok_url',
        'x_url',
        'logo',
        'favicon',
        'hero_background',
        'meta_title',
        'meta_description',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public static function current(): ?self
    {
        return static::first();
    }

    public function contentValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->content ?? [], $key, $default);
    }
}
