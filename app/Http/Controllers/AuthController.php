<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Đăng ký user mới
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:5|confirmed',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
        ]);
        $user->assignRole('user'); // hoặc 'admin'


        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    // Đăng nhập
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string', // nếu không dùng email thì dùng name
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('name', 'password'))) {
            throw ValidationException::withMessages([
                'name' => ['Tên đăng nhập hoặc mật khẩu không đúng.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }


    // Đăng xuất
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Đã đăng xuất']);
    }
}
