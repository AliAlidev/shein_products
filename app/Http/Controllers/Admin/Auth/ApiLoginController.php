<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApiLoginController extends Controller
{

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required']
        ]);
        try {
            $user = User::when(isset($data['number']), function ($qrt) use ($data) {
                $qrt->where('number', $data['number']);
            })->when(isset($data['email']), function ($qrt) use ($data) {
                $qrt->where('email', $data['email']);
            })->first();
            if (!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
            $user->token = $this->generateToken($user);
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function loginWithoutPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ]);
        try {
            $user = User::when(isset($data['email']), function ($qrt) use ($data) {
                $qrt->where('email', $data['email']);
            })->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
            return response()->json([
                'success' => true,
                'data' => $this->generateToken($user)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function generateToken($user)
    {
        $token = $user->createToken('authToken');
        $token->token->expires_at = Carbon::now()->addYears(10);
        $token->token->save();
        return $token->accessToken;
    }
}
