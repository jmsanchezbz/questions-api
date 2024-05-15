<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function authenticated(Request $request)
    {
        return response()->json([
            'authenticated' => true,
            'message' => 'User authenticated'
        ]);
    }

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
                'status' => 'ok',
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

    public function editProfile(Request $request, $id)
    {
        try {
            $regData = $request->validate([
                'name' => 'required|string',
                'username' => ['required', 'string', Rule::unique('users')->ignore($id)],
                'email' => ['required', 'string', 'email', Rule::unique('users')->ignore($id)],
                'password' => 'nullable|min:4',
                'role' => ['required', 'in:user,admin']
            ]);

            $user = User::find($id);

            $userCtrl = new UserController();

            $user->name = $regData['name'];
            $user->username = $regData['username'];
            $user->email = $regData['email'];
            $user->updated_at = Carbon::now('Europe/Madrid');

            if (!is_null($request->input('password'))) {
                $user->password = Hash::make($regData['password']);
            }

            if ($userCtrl->isAdmin($request)) {
                $user->role = $regData['role'];
            }

            $isSaved = false;

            //El perfil se puede modificar por el usuario administrador o el propio usuario
            if ($userCtrl->isAdmin($request) || $request->user()->id == $id) {
                $isSaved = $user->update();

                return response()->json([
                    'status' => 'ok',
                    'message' => 'User updated ',
                    'user' => $user
                ]);
            } else {
                return response()->json([
                    'status' => 'ko',
                    'message' => "Unauthorized user profile modification id:{$request->user()->id}(role:{$request->user()->role}) try to modify id:{$id}"
                ]);
            }
        } catch (ValidationException $exception) {

            return response()->json([
                'status' => 'exception',
                'message' => 'Validation failed'.$exception,
                'exception' => $exception
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e
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
