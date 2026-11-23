<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations, HasSlug;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'artikul',
        'weight',
        'size',
        'packaging',
        'price',
        'description',
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }

    public function seo()
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    /**
     * Nom (uz/ru) va artikul bo'yicha qidiruv. Har bir so'z topilishi shart.
     * JSON qiymatlar MySQL'da utf8mb4_bin bo'lgani uchun LOWER() bilan solishtiriladi.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $words = preg_split('/\s+/u', trim(mb_substr($term, 0, 100)), -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($word));
            // O'zbekcha tutuq belgisining barcha ko'rinishlari bir xil topilsin: o'g'il, o‘g‘il
            $pattern = '%' . str_replace(["'", '`', '‘', '’', 'ʻ', 'ʼ'], '_', $escaped) . '%';

            $query->where(function (Builder $q) use ($pattern) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.uz'))) LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ru'))) LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereRaw("LOWER(artikul) LIKE ? ESCAPE '!'", [$pattern]);
            });
        }

        return $query;
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
        static::deleting(function ($product) {
            foreach ($product->images as $image) {
                if ($image->image) {
                    Storage::disk('public')->delete($image->image);
                }
            }
        });
    }
}
