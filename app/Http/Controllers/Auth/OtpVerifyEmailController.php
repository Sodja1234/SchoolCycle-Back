<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserRegisteredMail;
use Illuminate\Http\Request;
use App\Models\User;

class OtpVerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if (!$user->email_otp || now()->greaterThan($user->email_otp_expires_at)) {
            return response()->json(['message' => 'Code OTP expiré ou non généré.'], 400);
        }

        if ($request->otp != $user->email_otp) {
            return response()->json(['message' => 'Code OTP invalide.'], 401);
        }

        // Succès : réinitialise l'OTP
        $user->email_verified_at = now();
        $user->email_otp = null;
        $user->email_otp_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Email vérifié avec succès.',
        ]);
    }

    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email déjà vérifié.'], 400);
        }

        // Générer un nouvel OTP
        $otp = rand(100000, 999999);
        $user->email_otp = $otp;
        $user->email_otp_expires_at = now()->addMinutes(10);
        $user->save();

        // Envoi mail OTP AVEC destinataire
        \Mail::to($user->email)->send(new UserRegisteredMail($user, $otp));

        return response()->json([
            'message' => 'Un nouveau code OTP a été envoyé.',
        ]);
    }
}
