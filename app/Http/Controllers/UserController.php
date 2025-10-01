<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // <-- Tambahkan ini untuk validasi email unik saat update
use App\Services\LogActivityService;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua user.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,admin,kandidat',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        LogActivityService::addToLog("Created new user: {$user->email}", $request);

        return response()->json($user, 201);
    }

    /**
     * Menampilkan detail satu user.
     * GET /api/users/{id}
     */
    public function show(User $user)
    {
        // Laravel otomatis akan mencari user berdasarkan ID dari URL
        return response()->json($user);
    }

    /**
     * Mengupdate data user di database.
     * PUT/PATCH /api/users/{id}
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            // Email harus unik, tapi abaikan email user saat ini
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8', // Password opsional
            'role' => 'sometimes|required|in:super_admin,admin,kandidat',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update data user
        $user->update($request->except('password'));

        // Jika ada password baru, update passwordnya
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        LogActivityService::addToLog("Updated user: {$user->email}", $request);

        return response()->json($user);
    }

    /**
     * Menghapus user dari database.
     * DELETE /api/users/{id}
     */
    public function destroy(Request $request, User $user)
    {
        $email = $user->email;
        $user->delete();

        LogActivityService::addToLog("Deleted user: {$email}", $request);

        // Beri respons JSON dengan status 200 OK
        return response()->json(['message' => 'User successfully deleted'], 200);
    }
}