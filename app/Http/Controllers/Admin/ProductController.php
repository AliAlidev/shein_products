<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\BackendController;
use App\Http\Traits\FileTrait;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SheinNode;
use App\Services\RapidapiSheinNewService;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\str;

class ProductController extends BackendController
{
    use FileTrait;
    private RapidapiSheinNewService $rapidapiSheinService;
    public function __construct(RapidapiSheinNewService $rapidapiSheinService)
    {
        $this->rapidapiSheinService = $rapidapiSheinService;
        parent::__construct();
    }

    public function index(Request $request)
    {
        $sections = SheinNode::whereHas('products')->select('channel')->distinct('channel')->pluck('channel')->toArray();
        $sectionTypes = json_decode($this->getSectionTypes()->getContent(), true)['data'];
        $categories = json_decode($this->getCategories()->getContent(), true)['data'];

        if ($request->ajax()) {
            $products = $this->getFilteredProducts($request);
            return DataTables::of($products)
                ->addColumn('main_image', function ($product) {
                    $image = $product->images[0] ?? null;
                    return $image ? '<a data-fancybox="gallery-' . $product->id . '" href="' . $image . '">
                                <img src="' . $image . '" style="width: 50px; height: 50px; object-fit: cover;" />
                            </a>' : null;
                })
                ->addColumn('brand', function ($product) {
                    return $product->brand;
                })
                ->addColumn('category_en', function ($product) {
                    return $product->category->name_en;
                })
                ->addColumn('category_ar', function ($product) {
                    return $product->category->name_ar;
                })
                ->editColumn('price', function ($product) {
                    return $product->price . ' ' . ($product->currency == 'USD' ? '$' : $product->currency);
                })
                ->addColumn('en_name', function ($product) {
                    return Str::limit($product->en_name, 30);
                })
                ->addColumn('ar_name', function ($product) {
                    return Str::limit($product->ar_name, 30);
                })
                ->addColumn('en_description', function ($product) {
                    return Str::limit($product->en_description, 50);
                })
                ->addColumn('ar_description', function ($product) {
                    return Str::limit($product->ar_description, 50);
                })
                ->addColumn('in_app_view', function ($product) {
                    $checked = $product->view_in_app ? 'checked' : '';
                    return '<input class="form-control in-app-view" style="width:20px" type="checkbox" data-url="' . route("product.view_on_app_status", $product->id) . '"
                                data-product-id="' . $product->id . '" ' . $checked . '>';
                })
                ->addColumn('ar_brand', function ($product) {
                    return $product->brand?->brand_name_ar ?? null;
                })
                ->addColumn('en_brand', function ($product) {
                    return $product->brand?->brand_name_en ?? null;
                })
                ->addColumn('action', function ($product) {
                    $retAction = '';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon float-left btn-primary edit-product" data-url="' . route('product.details', $product->id) . '" data-edit-url="' . route('product.edit', $product->id) . '" data-toggle="tooltip" data-placement="top" title="Edit" ><i class="far fa-edit"></i></a>';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon ml-2  float-left btn-info show-product" data-url="' . route('product.details', $product->id) . '" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye"></i></a>';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon ml-2 float-left btn-danger delete-product" data-name="' . $product->en_name . '" data-token= "' . csrf_token() . '" data-url="' . route('product.delete', $product->id) . '" data-toggle="tooltip" data-placement="top" title="Delete" ><i class="fa fa-trash"></i></a>';
                    return $retAction;
                })
                ->rawColumns(['main_image', 'in_app_view', 'action'])
                ->make(true);
        }
        return view('admin.product.index', ['categories' => $categories, 'sections' => $sections, 'sectionTypes' => $sectionTypes]);
    }

    public function export(Request $request)
    {
        $filename = 'exports/products-export-' . now()->format('Y-m-d-H-i-s') . '-' . uniqid() . '.xlsx';
        Excel::store(new ProductsExport($this->getFilteredProducts($request)->get()), $filename, 'public');
        $url = Storage::disk('public')->url($filename);
        return response()->json([
            'success' => true,
            'download_url' => $url,
            'filename' => basename($filename),
            'message' => 'Export generated successfully'
        ]);
    }

    // function syncProducts()
    // {
    //     // SheinNode::get()->map(function ($node) {
    //     //     $this->rapidapiSheinService->insertProductsWitPagination($node->href_target, $node->goods_id);
    //     //     dd("done");
    //     //  });
    //     // if ($response['success'])
    //     //     return response()->json([
    //     //         'success' => true,
    //     //         'message' => 'Products have been synced successfully'
    //     //     ]);
    //     // else
    //     //     return response()->json([
    //     //         'success' => true,
    //     //         'message' => $response['message']
    //     //     ]);
    // }

    function syncNodesCommand($includedTabs = [])
    {
        $this->rapidapiSheinService->fetchAndStoreNodes($includedTabs);
        return 1;
    }

    function syncProductsCommand($channel = null)
    {
        SheinNode::when($channel, function ($q) use ($channel) {
            $q->where('channel', $channel);
        })->get()->map(function ($node) {
            $this->rapidapiSheinService->insertProductsWitPagination($node->href_target, $node->goods_id, $node->id);
        });
        return 1;
    }

    function syncProductsDailyCommand()
    {
        // get current nodes
        $allowedSections = ['Men', 'Women'];
        $allowedSectionTypes = ['Fall & Winter', 'Trends', 'Clothing', 'Tops', 'Bottoms', 'Sports & Outdoor', 'Swimwear', 'Extended Sizes', 'Baby 0-3Yrs', 'Fall & Winter', 'Sale'];
        $nodeIds = Product::groupBy('node_id')->pluck('node_id')->toArray();
        SheinNode::whereIn('id', $nodeIds)
            ->whereIn('channel', $allowedSections)
            ->whereIn('root_name', $allowedSectionTypes)
            ->get()->map(function ($node) {
                $this->rapidapiSheinService->insertProductsWitPagination($node->href_target, $node->goods_id, $node->id);
            });
        return 1;
    }

    function changeViewProductOnAppStatus($id)
    {
        $product = Product::find($id);
        $product->view_in_app == 1 ? $product->view_in_app = 0 : $product->view_in_app = 1;
        $product->update();
        return response()->json([
            'success' => true,
            'message' => 'Product view on app status changed successfully'
        ]);
    }

    function exportCurrentPage(Request $request)
    {
        $query = $this->getFilteredProducts($request);
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('en_name', 'like', '%' . $request->search . '%')
                    ->orWhere('ar_name', 'like', '%' . $request->search . '%')
                    ->orWhere('en_description', 'like', '%' . $request->search . '%')
                    ->orWhere('ar_description', 'like', '%' . $request->search . '%')
                    ->orWhere('en_brand', 'like', '%' . $request->search . '%')
                    ->orWhere('ar_brand', 'like', '%' . $request->search . '%')
                    ->orWhere('store', 'like', '%' . $request->search . '%')
                    ->orWhere('barcode', 'like', '%' . $request->search . '%')
                    ->orWhere('price', 'like', '%' . $request->search . '%')
                    ->orWhere('creation_date', 'like', '%' . $request->search . '%');
            });
        }

        // Get the current page data
        $products = $query->skip(($request->page - 1) * $request->per_page)
            ->take($request->per_page)
            ->get();
        $filename = 'exports/products-export-' . now()->format('Y-m-d-H-i-s') . '-' . uniqid() . '.xlsx';
        Excel::store(new ProductsExport($products, true), $filename, 'public');

        // Generate temporary URL (valid for 24 hours by default)
        $url = Storage::disk('public')->url($filename);
        return response()->json([
            'success' => true,
            'download_url' => $url,
            'filename' => basename($filename),
            'message' => 'Export generated successfully'
        ]);
    }

    function getFilteredProducts($request)
    {
        $categoryIds = $request->category_ids;
        $sectionTypesFilter = $request->section_types;
        $sectionsFilter = $request->sections;
        return Product::when(isset($categoryIds) && count($categoryIds) > 0, function ($qrt) use ($categoryIds) {
            $qrt->whereIn('category_id', $categoryIds);
        })->when($sectionTypesFilter, function ($product) use ($sectionTypesFilter) {
            $product->whereHas('node', function ($node) use ($sectionTypesFilter) {
                $node->whereIn('root_name', $sectionTypesFilter);
            });
        })->when($sectionsFilter, function ($product) use ($sectionsFilter) {
            $product->whereHas('node', function ($node) use ($sectionsFilter) {
                $node->whereIn('channel', $sectionsFilter);
            });
        })->orderBy('created_at', 'desc');
    }

    function getProductDetails($id)
    {
        $product = Product::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product details fetched successfully'
        ]);
    }

    function editProduct(Request $request)
    {
        $data = $request->except(['_token']);
        $productId = $data['product_id'];
        unset($data['product_id']);
        $data['view_in_app'] = isset($data['view_in_app']) ? true : false;
        $productModel = Product::find($productId);
        if (isset($data['images'])) {
            // delete old images
            foreach ($productModel->images ?? [] as $key => $image) {
                $this->deleteFile($image);
            }
            $imagesList = [];
            foreach ($data['images'] as $key => $image) {
                $imagesList[] = $this->uploadFile($image, 'products');
            }
            $data['images'] = $imagesList;
        }
        $productModel->update($data);
        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    }

    function deleteProduct($id)
    {
        $product =  Product::find($id);
        foreach ($product->images ?? [] as $key => $image) {
            $this->deleteFile($image);
        }
        $product->delete();
        return redirect()->back()->with(['success' => 'Product deleted successfully']);
    }

    public function create(Request $request)
    {
        $data = $request->except(['_token']);
        if (Product::where('en_name', $data['en_name'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Product is already exists'
            ]);
        }
        $data['view_in_app'] = isset($data['view_in_app']) ? true : false;
        if (isset($data['images'])) {
            $imagesList = [];
            foreach ($data['images'] as $key => $image) {
                $imagesList[] = $this->uploadFile($image, 'products');
            }
            $data['images'] = $imagesList;
        }
        Product::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully'
        ]);
    }

    function getSectionTypes($channel = null)
    {
        // $lang = getCurrentLanguage();
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

    function getCategories($channel = null, $sectionType = null)
    {
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
}
