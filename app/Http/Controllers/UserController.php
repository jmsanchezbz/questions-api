<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
    public function findAll(Request $request)
    {
        try {
            $users =  User::all()->sortBy('id');
            return response()->json($users, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function findById($id)
    {
        try {
            $user =  User::find($id);

            if (!empty($user)) {
                return response()->json($user, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    "message" => "User not found"
                ], 404);
            }

            return response()->json($user, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'ko',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public static function isAdmin(Request $request)
    {
        $isAdmin = false;

        if ($request->user()->role == 'admin') {
            $isAdmin = true;
        }

        return $isAdmin;
    }
}
