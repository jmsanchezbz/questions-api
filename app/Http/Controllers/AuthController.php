<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $regData = $request->validate([
                'name' => 'required|string',
                'username' => 'required|string|unique:users',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|min:4'
            ]);

            $user = User::create([
                'name' => $regData['name'],
                'username' => $regData['username'],
                'email' => $regData['email'],
                'password' => Hash::make($regData['password']),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created '
            ]);
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $loginData = $request->validate([
                'username' => 'required|string',
                'password' => 'required|min:4'
            ]);

            $user = User::where('username', $loginData['username'])->first();

            if (!$user || !Hash::check($loginData['password'], $user->password)) {
                return response()->json([
                    'message' => 'Invalid Credentials'
                ], 401);
            }

            $token = $user->createToken($user->name . '-auth-token')->plainTextToken;
            return response()->json([
                'username' => $user->username,
                'role' => $user->role,
                'access_token' => $token,
            ]);
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            "status" => "ok",
            "message" => "logged out"
        ], 200);
    }
}
