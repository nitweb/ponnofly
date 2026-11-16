<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'path', 'sort_order'])]
class ProductImage extends Model
{
    public function getUrlAttribute(): string
    {
        // Support full remote URLs (imported products from external sites)
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return asset($this->path);
    }
}
