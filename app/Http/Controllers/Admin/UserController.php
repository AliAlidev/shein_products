<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Auth\ApiLoginController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query();
        if ($request->ajax()) {
            return DataTables::of($users)
                ->addColumn('role', function ($user) {
                    return $user->is_admin ? 'Admin' : 'Api User';
                })
                ->addColumn('action', function ($user) {
                    $retAction = '';
                    $retAction .= $user->is_admin ? '' : '<a href="#" class="btn btn-sm btn-icon float-left btn-success generate-token"
                                        data-url="' . route('users.generate_token', $user->id) . '"
                                        data-toggle="tooltip"
                                        data-placement="top"
                                        title="Generate Token Sensitive Data Please Do Not Share">
                                        <i class="fas fa-key"></i>
                                    </a>';
                    return $retAction;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.users.index');
    }

    function generateToken(Request $request, $id)
    {
        $user = User::find($id);
        $request->merge([
            'email' => $user->email
        ]);
        return app(ApiLoginController::class)->loginWithoutPassword(request());
    }
}
