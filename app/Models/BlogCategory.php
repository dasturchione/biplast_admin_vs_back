<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasTranslations;
    public array $translatable = ['name'];
    protected $fillable = [
        'name',
        'is_active'
    ];

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    
}
