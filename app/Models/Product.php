<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'images' => 'array',
        'additional_data' => 'array'
    ];

    function primaryImage()
    {
        return $this->images[0] ?? null;
    }

    function details()
    {
        return $this->hasOne(ProductDetail::class, 'product_id');
    }

    function brand()
    {
        return $this->hasOne(ProductBrand::class, 'id', 'brand_id');
    }

    function category()
    {
        return $this->hasOne(ProductCategory::class, 'id', 'category_id');
    }
}
