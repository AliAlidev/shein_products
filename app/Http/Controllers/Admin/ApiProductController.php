<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SheinNode;
use Illuminate\Http\Request;

class ApiProductController extends Controller
{
    function list(Request $request)
    {
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $categoryId = $request->category_id ?? null;
        $products = Product::orderBy('created_at', 'desc')->whereHas('details', function ($qrt) {
            $qrt->whereJsonLength('coupon_prices','>', 0);
        })->with([
            'details',
            'brand',
            'category'
        ])->when($categoryId, function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        })->where('view_in_app', 0)->paginate($perPage, ['*'], 'page', $page);
        return (ProductResource::collection($products))->additional(['success' => true, 'message' => ''])->response();
    }

    function getSectionTypes(Request $request)
    {
        $channel = $request->channel;
        $channel = is_string($channel) ? explode(",", $channel) : $channel;
        $sectionTypes = SheinNode::when($channel, function ($qrt) use ($channel) {
            $qrt->whereIn('channel', $channel);
        })->whereHas('products')->distinct('root_name')
            ->pluck('root_name')
            ->toArray();
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $sectionTypes
        ]);
    }

    function getCategories(Request $request)
    {
        $channel = $request->channel;
        $sectionType = $request->section_type;
        $channel = is_string($channel) ? explode(",", $channel) : $channel;
        $sectionType = is_string($sectionType) ? explode(",", $sectionType) : $sectionType;
        $lang = getCurrentLanguage();
        $nodeIds = SheinNode::when($channel, function ($query) use ($channel) {
            $query->where('channel', $channel);
        })->when($sectionType, function ($query) use ($sectionType) {
            $query->where('root_name', $sectionType);
        })->pluck('id')->toArray();
        $categoriesIds = Product::whereHas('node', function ($query) use ($nodeIds) {
            $query->whereIn('id', $nodeIds);
        })->distinct('category_id')->pluck('category_id')->toArray();
        $categories = ProductCategory::whereIn('external_id', $categoriesIds)->pluck('name_' . $lang, 'external_id')->toArray();
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $categories
        ]);
    }

    function getSections()
    {
        $sections = SheinNode::whereHas('products')->select('channel')->distinct('channel')->pluck('channel')->toArray();
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $sections
        ]);
    }
}
