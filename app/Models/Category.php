<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations, HasSlug;

    public array $translatable = ['name'];
    protected $fillable = ['name', 'slug', 'sort_order', 'image', 'is_active'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public static function nextSortOrder(): int
    {
        return ((int) static::max('sort_order')) + 1;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn($model) => $model->getTranslation('name', 'ru'))
            ->saveSlugsTo('slug')
            ->usingLanguage('ru');
    }

    protected static function booted()
    {
        // Import orqali yaratilgan kategoriyalar ham ro'yxat oxiriga tushadi
        static::creating(function ($category) {
            $category->sort_order ??= static::nextSortOrder();
        });

        static::deleting(function ($image) {
            if ($image->image) {
                Storage::disk('public')->delete($image->image);
            }
        });
    }
}
