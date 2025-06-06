<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceRule extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'apply_to' => 'array'
    ];
}
