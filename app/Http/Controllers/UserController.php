<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('kelas')->orderBy('nama')->get();
        $kelasList = Kelas::orderBy('nama')->get();

        return view('users', compact('users', 'kelasList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,km,guru'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);

        $data['kelas_id'] = $data['role'] === 'km' ? ($data['kelas_id'] ?? null) : null;
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('users')->with('pesan', "User {$data['username']} berhasil dibuat.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('users')->with('pesan', "Password user {$user->username} berhasil direset.");
    }
}
