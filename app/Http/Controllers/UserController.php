<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public static function isAdmin(Request $request)
    {
        $isAdmin = false;

        if ($request->user()->role == 'admin') {
            $isAdmin = true;
        }

        return $isAdmin;
    }
}
