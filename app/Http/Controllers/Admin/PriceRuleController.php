<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PriceRule;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities\Price;
use Yajra\DataTables\DataTables;

class PriceRuleController extends Controller
{
    public function index(Request $request)
    {
        $priceRules = PriceRule::query();
        if ($request->ajax()) {
            return DataTables::of($priceRules)
                ->addColumn('action', function ($priceRule) {
                    $retAction = '';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon float-left btn-primary edit-price-rule" data-url="' . route('price_rules.edit', $priceRule->id) . '" data-toggle="tooltip" data-placement="top" title="Edit" ><i class="far fa-edit"></i></a>';
                    $retAction .= '<a href="#" class="btn btn-sm btn-icon ml-2 float-left btn-danger delete-price-rule" data-name="' . $priceRule->name . '" data-token= "' . csrf_token() . '" data-url="' . route('price_rules.delete', $priceRule->id) . '" data-toggle="tooltip" data-placement="top" title="Delete" ><i class="fa fa-trash"></i></a>';
                    return $retAction;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.price_rule.index');
    }

    function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'apply_per' => 'required',
            'apply_to' => 'nullable',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric'
        ], [
            'name.required' => 'Name is required',
            'apply_per.required' => 'Apply per is required',
            'apply_to.required' => 'Apply to is required',
            'type.required' => 'Type is required',
            'value.required' => 'The value field is required.',
            'value.numeric' => 'The value must be a number.',
            'value.max' => 'Percentage value cannot exceed 100.', // custom message for max
        ]);

        $validator->sometimes('value', 'max:100', function ($input) {
            return $input->type === 'percentage';
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validate();
        if (isset($data['apply_per']) && $data['apply_per'] == 'Default' && PriceRule::where('apply_per', 'Default')->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Default price rule already exists'
            ]);
        }
        PriceRule::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Price rule created successfully'
        ]);
    }

    function getCategories()
    {
        $lang = getCurrentLanguage();
        $categories = ProductCategory::pluck('name_' . $lang, 'external_id')->toArray();
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $categories
        ]);
    }

    function getProducts()
    {
        $lang = getCurrentLanguage();
        $categories = Product::pluck($lang . '_name', 'external_id')->toArray();
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $categories
        ]);
    }

    function update(Request $request, $id)
    {
        $priceRule = PriceRule::find($id);
        if ($request->method() == 'POST') {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'apply_per' => 'required',
                'apply_to' => 'nullable',
                'type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric'
            ], [
                'name.required' => 'Name is required',
                'apply_per.required' => 'Apply per is required',
                'apply_to.required' => 'Apply to is required',
                'type.required' => 'Type is required',
                'value.required' => 'The value field is required.',
                'value.numeric' => 'The value must be a number.',
                'value.max' => 'Percentage value cannot exceed 100.', // custom message for max
            ]);

            $validator->sometimes('value', 'max:100', function ($input) {
                return $input->type === 'percentage';
            });

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validate();
            if (isset($data['apply_per']) && $data['apply_per'] == 'Default' && PriceRule::where('apply_per', 'Default')->where('id', '!=', $id)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Default price rule already exists'
                ]);
            }
            $priceRule->update($data);
            return response()->json([
                'success' => true,
                'message' => 'Price rule updated successfully'
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $priceRule
        ]);
    }

    function delete($id)
    {
        $priceRule = PriceRule::find($id);
        $priceRule->delete();
        return response()->json([
            'success' => true,
            'message' => 'Price rule deleted successfully'
        ]);
    }
}
