<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'product_material' => 'array',
        'promotion_info' => 'array',
        'related_color_new' => 'array',
        'featureSubscript' => 'array'
    ];
}
