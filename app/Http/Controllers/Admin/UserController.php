<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => new User(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->saveUser($request, new User());
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        return $this->saveUser($request, $user);
    }

    private function saveUser(Request $request, User $user)
    {
        $isNew = ! $user->exists;

        $emailRule = Rule::unique('users', 'email');
        $nimRule = Rule::unique('users', 'nim');

        if (! $isNew) {
            $emailRule->ignore($user->id);
            $nimRule->ignore($user->id);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'role' => [
                'required',
                Rule::in(['admin', 'petugas', 'mahasiswa']),
            ],
            'nim' => [
                'exclude_unless:role,mahasiswa',
                'required',
                'string',
                'max:30',
                $nimRule,
            ],
            'is_active' => ['required', 'boolean'],
            'password' => [
                $isNew ? 'required' : 'nullable',
                'string',
                'min:8',
                'max:72',
                'confirmed',
            ],
        ], [
            'required' => ':attribute wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'nim.unique' => 'NIM sudah digunakan akun lain.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 72 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ], [
            'name' => 'Nama',
            'email' => 'Email',
            'role' => 'Peran',
            'nim' => 'NIM',
            'is_active' => 'Status akun',
            'password' => 'Password',
        ]);

        $isSelf = ! $isNew
            && $user->id === $request->user()->id;

        if (
            $isSelf
            && (
                $data['role'] !== 'admin'
                || ! $request->boolean('is_active')
            )
        ) {
            throw ValidationException::withMessages([
                'role' => 'Kamu tidak boleh mengubah peran atau menonaktifkan akunmu sendiri.',
            ]);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];

        // Penetapan peran dan status dilakukan secara eksplisit.
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');

        $user->nim = $data['role'] === 'mahasiswa'
            ? $data['nim']
            : null;

        if (! empty($data['password'])) {
            // Model User sudah memiliki cast password => hashed.
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                $isNew
                    ? 'Akun berhasil ditambahkan.'
                    : 'Akun berhasil diperbarui.'
            );
    }
}