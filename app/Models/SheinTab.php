<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheinTab extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts=[
        'cat_ids' => 'array'
    ];
}
