<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DarkModeController extends Controller
{
    public function toggleDarkMode(Request $request)
    {
        $user = Auth::user();
        $user->dark_mode = $request->dark_mode;
        $user->save();

        return response()->json(['success' => true]);
    }
}
