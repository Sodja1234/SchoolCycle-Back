<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    /**
     * Vérifie l'e-mail de l'utilisateur authentifié.
     * Ce contrôleur est appelé automatiquement lorsqu'un utilisateur clique sur le lien de vérification reçu par email.
     *
     * @param EmailVerificationRequest $request Requête spéciale contenant le lien de vérification
     * @return JsonResponse Réponse JSON avec le statut de la vérification
     */
    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
        // ✅ Vérifie si l’e-mail est déjà vérifié
        if ($request->user()->hasVerifiedEmail()) {
            // Si oui, on retourne un statut indiquant que l’e-mail est déjà vérifié
            return response()->json(['status' => 'verification-link-already']);
        }

        // ✅ Marque l’e-mail comme vérifié si ce n’est pas encore le cas
        if ($request->user()->markEmailAsVerified()) {
            // Déclenche l’événement Laravel standard `Verified`
            event(new Verified($request->user()));
        }

        // ✅ Réponse en cas de succès
        return response()->json(['status' => 'verification-link-success']);
    }
}
