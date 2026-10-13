<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EffectifPortail;
use App\Support\Portail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Le portail nous envoie ici l'effectif de l'institut, a chaque fois qu'un
 * acces change chez lui. La liste est complete : elle fait foi.
 */
class PortalRosterController extends Controller
{
    public function __invoke(Request $request, EffectifPortail $effectif): JsonResponse
    {
        try {
            Portail::verifier($request->bearerToken(), 'effectif');
        } catch (Throwable $e) {
            Log::warning('Effectif portail : accès refusé.', ['raison' => $e->getMessage()]);

            return response()->json(['message' => 'Accès refusé.'], 401);
        }

        $resultat = $effectif->appliquer($request->json()->all());

        Log::info('Effectif portail reçu.', $resultat);

        return response()->json(['resultat' => $resultat]);
    }
}
