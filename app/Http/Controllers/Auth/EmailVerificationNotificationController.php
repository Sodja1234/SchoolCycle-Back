<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse
    {

        //verification si l'utilisateur à ete verifié (que l'email est deja verifié)
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['status' => 'verification-link-already']);
        }

        //envoie du lien de verification
        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
        
    }
}
