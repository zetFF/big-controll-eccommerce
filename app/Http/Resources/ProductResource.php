<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'primary_image' => new ProductImageResource($this->whenLoaded('primaryImage')),
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 