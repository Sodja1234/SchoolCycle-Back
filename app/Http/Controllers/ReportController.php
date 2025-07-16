<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Resources\ReportResource;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Enregistre un nouveau signalement d'annonce.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'announcement_id' => ['required', 'exists:announcements,id'],
            'motif' => ['required', 'string', 'max:255'],
            'detail' => ['nullable', 'string'],
        ]);

        $report = Report::create($validated);

        // Charger les relations pour que la resource les utilise
        $report->load(['user', 'announcement']);

        return response()->json([
            'message' => 'Le signalement a été enregistré avec succès.',
            'data' => new ReportResource($report),
        ], 201);
    }

    /**
     * Retourne la liste des signalements avec les relations utilisateur et annonce.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $reports = Report::with(['user', 'announcement'])->latest()->paginate(5);
        return  ReportResource::collection($reports);
    }

    public function destroy(Report $report){
        $user = Auth::user();
        try {
            if ($user->role !=='admin') {
                return response()->json([
                    'Message' => "Vous n'avez pas le droit de supprimer",
                ], 403);
            }
            else{
                $report->delete();
                return Response()->json([
                    "Message"=>"[]"
                ]);
            }
        }catch(\Exception $exception){
            return response()->json([
                'Message' => "Une erreur est survenue lors de la suppression",
                'Erreur' => $exception->getMessage()
            ], 500);
        }
    }
}
