<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author' => [
                'name' => $this->author->name,
                'role' => "Admin"
            ],
            'category_uz' => $this->category ? $this->category->getTranslation('name', 'uz') : null,
            'category_ru' => $this->category ? $this->category->getTranslation('name', 'ru') : null,
            'title_uz' => $this->getTranslation('title', 'uz'),
            'title_ru' => $this->getTranslation('title', 'ru'),
            'slug' => $this->slug,
            'excerpt_uz' => $this->getTranslation('excerpt', 'uz'),
            'excerpt_ru' => $this->getTranslation('excerpt', 'ru'),
            'content_uz' => $this->getTranslation('content', 'uz'),
            'content_ru' => $this->getTranslation('content', 'ru'),
            'featured_image' => $this->featured_image ? asset('storage/' . $this->featured_image) : null,
            'published_at' => $this->published_at,
        ];
    }
}
