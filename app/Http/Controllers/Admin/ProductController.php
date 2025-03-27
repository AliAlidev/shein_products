<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Bank;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Requests\BankRequest;
use App\Http\Controllers\BackendController;
use App\Models\Product;

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
                    $image = $product->main_image ?? 'https://via.placeholder.com/50';
                    return '<img src="' . $image . '" alt="' . $product->name . '" style="width: 50px; height: 50px; object-fit: cover;">';
                })
                ->addColumn('brand', function ($product) {
                    return $product->brand;
                })
                ->addColumn('in_app_view', function ($product) {
                    $checked = $product->in_app_view ? 'checked' : '';
                    return '<div class="form-check form-switch">
                <input class="form-check-input in-app-view" type="checkbox"
                    data-product-id="' . $product->id . '" ' . $checked . '>
            </div>';
                })
                ->addColumn('action', function ($product) {
                    $retAction = '';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon float-left btn-primary" data-toggle="tooltip" data-placement="top" title="Edit" ><i class="far fa-edit"></i></a>';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon ml-2  float-left btn-info" data-toggle="tooltip" data-placement="top" title="View"><i class="far fa-eye"></i></a>';
                    return $retAction;
                })
                ->rawColumns(['main_image', 'in_app_view', 'action'])
                ->make(true);
        }
        return view('admin.product.index', $this->data);
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


    public function edit($id)
    {
        // $this->data['users'] = User::role([3, 4])->with('roles')->latest()->get();
        // $this->data['bank'] = Bank::findOrFail($id);
        // return view('admin.bank.edit', $this->data);
    }


    public function update(Request $request, Product $bank)
    {
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
        // return redirect(route('admin.bank.index'))->withSuccess('The data updated successfully.');
    }

    public function show(Product $bank)
    {
        return view('admin.bank.show', compact('bank'));
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return redirect(route('admin.bank.index'))->withSuccess('The data deleted successfully.');
    }
}
