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
        'additional_data' => 'array',
        'parent_categories' => 'array'
    ];

    public function scopeSearch($query, $term)
    {
        $term = $term . '%';
        return $query->where(function ($q) use ($term) {
            $q->where('ar_name', 'LIKE', $term)
                ->orWhere('en_name', 'LIKE', $term)
                ->orWhere('slug', 'LIKE', $term)
                ->orWhere('external_sku', 'LIKE', $term)
                ->orWhere('price', 'LIKE', $term)
                ->orWhere('currency', 'LIKE', $term)
                ->orWhere('ar_description', 'LIKE', $term)
                ->orWhere('en_description', 'LIKE', $term)
                ->orWhere('store', 'LIKE', $term)
                ->orWhere('barcode', 'LIKE', $term)
                ->orWhere('video_url', 'LIKE', $term)
                ->orWhere('mall_code', 'LIKE', $term);
        });
    }


    function primaryImage()
    {
        return $this->primary_image ?? $this->images[0] ?? null;
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
        return $this->hasOne(ProductCategory::class, 'external_id', 'category_id');
    }

    function node()
    {
        return $this->hasOne(SheinNode::class, 'id', 'node_id');
    }
}
