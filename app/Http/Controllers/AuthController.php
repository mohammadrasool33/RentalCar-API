<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'required_fields' => [
                        'email' => 'Your email address',
                        'password' => 'Your password'
                    ]
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                    'required_fields' => [
                        'email' => 'Your email address',
                        'password' => 'Your password'
                    ]
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'admin' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during login',
                'error' => $e->getMessage(),
                'required_fields' => [
                    'email' => 'Your email address',
                    'password' => 'Your password'
                ]
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            '_id' => $user->id,
            'email' => $user->email,
            'createdAt' => $user->created_at->toIso8601String(),
            'updatedAt' => $user->updated_at->toIso8601String(),
        ]);
    }
} 