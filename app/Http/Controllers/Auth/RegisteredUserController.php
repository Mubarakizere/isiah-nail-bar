<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle the customer registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // ✅ Validate form input
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone'    => ['required', 'string', 'min:10'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 🔁 Prevent duplicate customer profile
        if (Customer::where('email', $request->email)->exists()) {
            return back()->withInput()->with('error', 'This email is already associated with a customer account.');
        }

        // 👤 Create user account
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 🗂️ Create matching customer profile
        $customer = Customer::create([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        // 🔗 Link customer to user
        $user->customer_id = $customer->id;
        $user->save();

        // 🔐 Assign role to user
        $user->assignRole('customer');

        // 📩 Trigger email verification if enabled
        event(new Registered($user));

        // 🔓 Auto-login
        Auth::login($user->fresh());

        // 🚀 Redirect to dashboard
        return redirect()->intended(route('dashboard.customer'));
    }
}
