<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ForgotPasswordController extends Controller
{
    // Method untuk handle permintaan reset password
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate token
        $token = Str::random(60);

        // Simpan token ke tabel password_resets
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Kirim email (Simulasi, karena kita tidak setup email server)
        // Di aplikasi nyata, di sini  akan kirim email berisi token dan link ke frontend
        // Contoh link: https://frontend-kamu.com/reset-password?token=TOKEN_ASLI_DISINI

        return response()->json([
            'message' => 'Password reset request has been sent. Please check your email.',
            'reset_token_for_testing' => $token // HANYA UNTUK TESTING, HAPUS DI PRODUKSI
        ]);
    }

    // Method untuk handle reset password dengan token
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $resetData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Validasi token
        if (!$resetData || !Hash::check($request->token, $resetData->token)) {
            return response()->json(['error' => 'Invalid token or email.'], 400);
        }

        // Update password user
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Hapus token setelah digunakan
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been successfully reset.']);
    }
}