<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Portail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Le portail vient chercher ici le catalogue des rôles et le personnel,
 * muni d'un jeton signé avec le secret partagé.
 */
class PortalDirectoryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            Portail::verifier($request->bearerToken(), 'annuaire');
        } catch (Throwable $e) {
            Log::warning('Annuaire portail : accès refusé.', ['raison' => $e->getMessage()]);

            return response()->json(['message' => 'Accès refusé.'], 401);
        }

        return response()->json(Portail::annuaire());
    }
}
