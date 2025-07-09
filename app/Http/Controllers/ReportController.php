<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Resources\ReportResource;

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
        $reports = Report::with(['user', 'announcement'])->latest()->get();
        return response()->json([
            "data"=> ReportResource::collection($reports)
        ]);
    }
}
