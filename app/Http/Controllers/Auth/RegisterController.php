<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Registration is disabled - only admin can create users
 * This controller is kept for potential future use
 */
class RegisterController extends Controller
{
    /**
     * Registration is disabled
     * Only admins can create users through the user management system
     */
    public function showRegistrationForm()
    {
        return redirect()->route('login')->with('error', 'التسجيل غير متاح. يرجى التواصل مع المدير.');
    }

    /**
     * Registration is disabled
     */
    public function register(Request $request)
    {
        return redirect()->route('login')->with('error', 'التسجيل غير متاح. يرجى التواصل مع المدير.');
    }
}
