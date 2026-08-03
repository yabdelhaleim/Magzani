<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and is active
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['الحساب غير نشط. يرجى التواصل مع المدير'],
            ]);
        }

        // Attempt to login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Super-admin accounts land on the central dashboard; everyone
            // else falls back to whatever the redirect target was, or the
            // tenant dashboard.
            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('super-admin.dashboard'))
                    ->with('success', 'مرحباً بك '.$user->name);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'مرحباً بك '.$user->name);
        }

        throw ValidationException::withMessages([
            'email' => ['بيانات الدخول غير صحيحة'],
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Path-relative redirect — NOT redirect()->route('login'). The latter would resolve
        // to the absolute URL of the FIRST-REGISTERED route named "login", which now lives on
        // a per-central-domain registration and would bounce a tenant user (e.g. on
        // kayan.remotelly1.site) over to superdashboard.remotelly1.site after logout. A
        // relative "/login" lands each host on its own /login form: superdashboard (and the
        // other central domains) hit the per-domain web route, kayan hit routes/tenant.php.
        return redirect('/login')->with('success', 'تم تسجيل الخروج بنجاح');
    }
}
