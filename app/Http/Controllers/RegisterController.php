<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // Normalisasi email jika inputnya berupa teks.
        if (is_string($request->input('email'))) {
            $request->merge([
                'email' => Str::lower(trim($request->input('email'))),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => [
                'required',
                'string',
                'max:30',
                'unique:users,nim',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 72 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->nim = $data['nim'];
        $user->email = $data['email'];

        // Otomatis di-hash oleh cast pada model User.
        $user->password = $data['password'];

        // Peran dan status selalu ditentukan oleh server.
        $user->role = 'mahasiswa';
        $user->is_active = true;

        $user->save();

        return redirect()
            ->route('login')
            ->with('success', 'Pendaftaran berhasil. Silakan login.')
            ->withInput([
                'email' => $user->email,
            ]);
    }
}