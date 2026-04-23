<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Blog extends Model
{
    use HasTranslations, HasSlug;
    public array $translatable = ['title', 'excerpt', 'content'];
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'blog_category_id',
        'is_published',
        'published_at',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function seo()
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn($model) => $model->getTranslation('title', 'ru'))
            ->saveSlugsTo('slug')
            ->usingLanguage('ru');
    }

    protected static function booted()
    {
        static::deleting(function ($image) {
            if ($image->featured_image) {
                Storage::disk('public')->delete($image->featured_image);
            }
        });
    }
}
