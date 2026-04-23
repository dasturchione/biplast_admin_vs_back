<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations, HasSlug;

    public array $translatable = ['name'];
    protected $fillable = ['name', 'slug', 'image', 'is_active'];

    public function products()
    {
        return $this->hasMany(Product::class);
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
        static::deleting(function ($image) {
            if ($image->image) {
                Storage::disk('public')->delete($image->image);
            }
        });
    }
}
