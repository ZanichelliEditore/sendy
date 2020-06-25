<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{

    public function logout()
    {
        return Auth::logout();
    }

    /**
     *  Logout from idp
     * @return void
     */
    public function logoutIdp(Request $request)
    {
        $userId = $request->input('id');
        DB::table('sessions')->where('user_id', $userId)->delete();
        return response()->json([], 200);
    }
}
