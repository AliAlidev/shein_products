<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ApiProductController extends Controller
{
    function list(Request $request)
    {
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $categoryId = $request->category_id ?? null;
        $products = Product::with([
            'details',
            'brand',
            'category'
        ])->when($categoryId, function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        })->where('view_in_app', 0)->paginate($perPage, ['*'], 'page', $page);
        return (ProductResource::collection($products))->additional(['success' => true, 'message' => ''])->response();
    }

    function categories()
    {
        $lang = getCurrentLanguage();
        $categories = ProductCategory::pluck('name_' . $lang, 'external_id')->toArray();
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $categories
        ]);
    }
}
