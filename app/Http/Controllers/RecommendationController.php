<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use App\Http\Requests\StoreRecommendationRequest;
use App\Http\Requests\UpdateRecommendationRequest;
use App\Models\Subtopic;
use Tymon\JWTAuth\Facades\JWTAuth;

class RecommendationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function recommendations(Subtopic $subtopic)
    {
        $student = JWTAuth::user()->student;
       $subtopic_student_status = $subtopic->studentEvaluations()->where('student_id', $student->id)->latest()->first();
        $recommendation_videos = $subtopic->videos()->get();
        return response()->json([
            'subtopic_status' => $subtopic_student_status ? $subtopic_student_status->evaluation_status : 'not attempted',
            'subtopic_difficulty' => $subtopic->subtopic_difficulty ?? null,
            'subtopic_title' => $subtopic->title,
            'subtopic_evaluation' => $subtopic_student_status ? $subtopic_student_status->subtopic_evaluation : null,
            'recommendations' => $recommendation_videos
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecommendationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Recommendation $recommendation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recommendation $recommendation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecommendationRequest $request, Recommendation $recommendation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recommendation $recommendation)
    {
        //
    }
}
