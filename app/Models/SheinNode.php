<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheinNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'root_name',
        'node_name',
        'nav_node_id',
        'cate_tree_node_id',
        'href_type',
        'href_target',
        'goods_id',
        'image_url'
    ];
}
