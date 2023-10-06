<?php

namespace App\Http\Controllers;

use App\Models\ResetCodePassword;
use App\Mail\SendCodeResetPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Hash; // Tambahkan ini untuk Hashing password
use App\Models\Player; // Gantikan dengan model yang sesuai dengan pengguna Anda

class ForgotPasswordController extends Controller
{
    /**
     * Send new password with random code to user's email to reset password
     *
     * @param  mixed $request
     * @return void
     */
    public function __invoke(ForgotPasswordRequest $request)
    {
        try {
            // Hapus kode reset sebelumnya jika ada
            ResetCodePassword::where('email', $request->email)->delete();

            // Generate password baru dengan panjang 8 karakter
            $newPassword = strtolower(str_random(8)); // Anda perlu memastikan Anda memiliki fungsi randomString yang sesuai

            // Simpan password baru dalam database
            $player = Player::where('email', $request->email)->firstOrFail();
            $player->update([
                "password" => Hash::make($newPassword)
            ]);

            // Kirim password baru ke email pengguna
            Mail::to($request->email)->send(new SendCodeResetPassword($newPassword));

            return $this->jsonResponse(null, trans('email sended'), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
