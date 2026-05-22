<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'nim'      => ['required', 'string', 'unique:users,nim'],
            'angkatan' => ['required', 'digits:4', 'integer', 'min:2000'],
            'gender'   => ['required', 'in:L,P'],
            'kelompok' => ['required', 'string'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            
            // TAMBAHKAN VALIDASI KODE DI SINI
            'registration_code' => [
                'required', 
                function ($attribute, $value, $fail) {
                    // GANTI "WTHME2025" dengan kode rahasia pilihan panitia
                    if ($value !== 'WTHME2025') {
                        $fail('Kode registrasi salah. Silakan hubungi panitia untuk mendapatkan kode.');
                    }
                }
            ],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'nim'       => $request->nim,
            'angkatan'  => $request->angkatan,
            'kelompok'  => $request->kelompok,
            'gender'    => $request->gender,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'peserta', 
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard'));
    }
}