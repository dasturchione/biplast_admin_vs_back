<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function PHPSTORM_META\map;

class ProductResource extends JsonResource
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
            'category' => [
                'name_uz' => $this->category ? $this->category->getTranslation('name', 'uz') : null,
                'name_ru' => $this->category ? $this->category->getTranslation('name', 'ru') : null,
            ],
            'name_uz' => $this->getTranslation('name', 'uz'),
            'name_ru' => $this->getTranslation('name', 'ru'),
            'slug' => $this->slug,
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_path' => asset('storage/' . $image->image),
                ];
            }),
            'description_uz' => $this->getTranslation('description', 'uz'),
            'description_ru' => $this->getTranslation('description', 'ru'),
            'price' => $this->price,
        ];
    }
}
