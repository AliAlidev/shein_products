<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Models\User;
use App\Models\Bank;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Requests\BankRequest;
use App\Http\Controllers\BackendController;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends BackendController
{

    public function __construct()
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Product';
    }

    public function index(Request $request)
    {
        $products = Product::get();
        if ($request->ajax()) {
            return DataTables::of($products)
                ->addColumn('main_image', function ($product) {
                    $image = $product->main_image ?? null;
                    return '<img src="' . $image . '" alt="' . $product->name . '" style="width: 50px; height: 50px; object-fit: cover;">';
                })
                ->addColumn('brand', function ($product) {
                    return $product->brand;
                })
                ->addColumn('in_app_view', function ($product) {
                    $checked = $product->view_in_app ? 'checked' : '';
                    return '<input class="form-control in-app-view" style="width:20px" type="checkbox" data-url="'.route("product.view_on_app_status", $product->id).'"
                                data-product-id="' . $product->id . '" ' . $checked . '>';
                })
                ->addColumn('action', function ($product) {
                    $retAction = '';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon float-left btn-primary edit-product" data-url="' . route('product.details', $product->id) . '" data-edit-url="' . route('product.edit', $product->id) . '" data-toggle="tooltip" data-placement="top" title="Edit" ><i class="far fa-edit"></i></a>';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon ml-2  float-left btn-info show-product" data-url="' . route('product.details', $product->id) . '" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye"></i></a>';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon ml-2 float-left btn-danger delete-product" data-name="' . $product->en_name . '" data-token= "'.csrf_token().'" data-url="' . route('product.delete', $product->id) . '" data-toggle="tooltip" data-placement="top" title="Delete" ><i class="fa fa-trash"></i></a>';
                    return $retAction;
                })
                ->rawColumns(['main_image', 'in_app_view', 'action'])
                ->make(true);
        }
        return view('admin.product.index', $this->data);
    }

    public function export(Request $request)
    {
        $filename = 'exports/products-export-' . now()->format('Y-m-d-H-i-s') . '-' . uniqid() . '.xlsx';
        Excel::store(new ProductsExport, $filename, 'public');
        $url = Storage::disk('public')->url($filename);
        return response()->json([
            'success' => true,
            'download_url' => $url,
            'filename' => basename($filename),
            'message' => 'Export generated successfully'
        ]);
    }

    function syncProducts()
    {
        sleep(2);
        return response()->json([
            'success' => true,
            'message' => 'Products have been synced successfully'
        ]);
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

    public function exportCurrentPage(Request $request)
    {
        $query = Product::query();
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
        Excel::store(new ProductsExport($products), $filename, 'public');

        // Generate temporary URL (valid for 24 hours by default)
        $url = Storage::disk('public')->url($filename);
        return response()->json([
            'success' => true,
            'download_url' => $url,
            'filename' => basename($filename),
            'message' => 'Export generated successfully'
        ]);
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
        Product::find($productId)->update($data);
        return redirect()->back()->with(['success' => 'Product updated successfully']);
    }

    function deleteProduct($id)
    {
        Product::find($id)->delete();
        return redirect()->back()->with(['success' => 'Product deleted successfully']);
    }

    public function create()
    {
        $this->data['users'] = User::role([3, 4])->with('roles')->latest()->get();
        return view('admin.product.create', $this->data);
    }


    public function store(Request $request)
    {
        // $bank                      = new Bank;
        // $bank->user_id             = $request->user_id;
        // $bank->bank_name           = $request->bank_name;
        // $bank->bank_code           = $request->bank_code;
        // $bank->recipient_name      = $request->recipient_name;
        // $bank->account_number      = $request->account_number;
        // $bank->mobile_agent_name   = $request->mobile_agent_name;
        // $bank->mobile_agent_number = $request->mobile_agent_number;
        // $bank->paypal_id           = $request->paypal_id;
        // $bank->upi_id              = $request->upi_id;
        // $bank->save();
        // return redirect(route('admin.bank.index'))->withSuccess('The data inserted successfully.');
    }



    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return redirect(route('admin.bank.index'))->withSuccess('The data deleted successfully.');
    }
}
