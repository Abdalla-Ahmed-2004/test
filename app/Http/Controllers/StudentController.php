<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\AiPrediction;
use App\Models\StudentSubtopicState;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AIEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $student = JWTAuth::user()->student;
        $lesson_attempts = $student->lessonAttempts()->where('quiz_attempted', true)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Student dashboard data retrieved successfully',
            'student' => new StudentResource($student),
            'lesson_attempts_completed_count' => $lesson_attempts->count(),
            'lesson_attempts' => $student->lessonAttempts->map(function ($attempt) use ($student) {
                $score = $attempt->quiz_id ? $student->quizzesAttempt()->where('quiz_id', $attempt->quiz_id)->value('score') : null;
                $total_marks = $attempt->quiz_id ? $attempt->quiz->total_marks : null;
                return [
                    'lesson_title' => $attempt->lesson->title,
                    'video_title' => $attempt->video->title,
                    'quiz_title' => $attempt->quiz ? $attempt->quiz->title : null,
                    'score' => $score,
                    'total_marks' => $attempt->quiz_id ? $attempt->quiz->total_marks : null,
                    'percentage' => $attempt->quiz_id && $total_marks ? round(($score / $total_marks) * 100, 2) : null,
                    'attempted_at' => $attempt->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            // You can add more data here as needed, such as recent quiz attempts, recommended lessons, etc.
        ]);
        // For now, just return a placeholder response

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }

    public function aiTest(Request $request, AIEvaluationService $aiService)
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            $payload = $request->all();
        }

        if (empty($payload)) {
            return response()->json([
                'error' => 'No prediction data received.',
            ], 400);
        }

        try {
            if (!is_array($payload)) {
                return response()->json([
                    'error' => 'Prediction payload must be an object or array.',
                ], 400);
            }

            $items = $this->normalizeAiTestItems($payload);

            // aiTest currently sends the feature rows directly to the AI API.
            // Database-based feature reconstruction stays commented in the service for later use.
            $preparedItems = $aiService->buildPredictionItems($items);

            if (empty($preparedItems)) {
                return response()->json([
                    'error' => 'No valid prediction items were found. Provide the full feature set inside items.',
                ], 400);
            }

            $response = Http::acceptJson()->timeout(10)->post(env('AI_PREDICTOR_URL'), [
                'items' => $preparedItems,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Save each prediction from the batch to the database tables.
                // $savedCount = 0;
                // if (!empty($responseData['data']) && is_array($responseData['data'])) {
                //     foreach ($responseData['data'] as $predictionResult) {
                //         // Find the corresponding request item to get all features.
                //         $requestItem = $this->findMatchingRequestItem($preparedItems, $predictionResult);

                //         if ($requestItem) {
                //             $userId = $predictionResult['user_id'] ?? $requestItem['user_id'] ?? null;
                //             $subtopicId = $predictionResult['skill_id'] ?? $requestItem['skill_id'] ?? null;

                //             if ($userId && $subtopicId) {
                //                 $saveData = [
                //                     'decayed_mastery' => $requestItem['decayed_mastery'] ?? 0.0,
                //                     'skill_difficulty_avg' => $requestItem['skill_difficulty_avg'] ?? 0.0,
                //                     'student_skill_history' => $requestItem['student_skill_history'] ?? 0.0,
                //                     'user_success_rate' => $requestItem['user_success_rate'] ?? 0.0,
                //                     'weighted_streak' => $requestItem['weighted_streak'] ?? 0.0,
                //                     'consecutive_correct' => $requestItem['consecutive_correct'] ?? 0,
                //                     'opportunity_count' => $requestItem['opportunity_count'] ?? 0,
                //                     'learning_momentum' => $requestItem['learning_momentum'] ?? 0.0,
                //                     'weighted_consistency' => $requestItem['weighted_consistency'] ?? 0.0,
                //                     'total_experience_score' => $requestItem['total_experience_score'] ?? 0.0,
                //                     'complexity_gap' => $requestItem['complexity_gap'] ?? 0.0,
                //                     'mastery_history_gap' => $requestItem['mastery_history_gap'] ?? 0.0,
                //                     'performance_efficiency' => $requestItem['performance_efficiency'] ?? 0.0,
                //                     'consistency_success_sync' => $requestItem['consistency_success_sync'] ?? 0.0,
                //                     'prediction_probability' => $predictionResult['prob'] ?? null,
                //                     'prediction_status' => $predictionResult['status'] ?? null,
                //                 ];

                //                 // Save to AiPrediction table
                //                 AiPrediction::updateOrCreate(
                //                     [
                //                         'user_id' => $userId,
                //                         'subtopic_id' => $subtopicId,
                //                     ],
                //                     $saveData
                //                 );

                //                 // Save to StudentSubtopicState table
                //                 StudentSubtopicState::updateOrCreate(
                //                     [
                //                         'user_id' => $userId,
                //                         'subtopic_id' => $subtopicId,
                //                     ],
                //                     $saveData
                //                 );

                //                 $savedCount++;
                //             }
                //         }
                //     }
                // }

                return response()->json([
                    'message' => 'Connected to AI successfully!',
                    // 'saved_predictions' => $savedCount,
                    'ai_response' => $responseData,
                ], $response->status());
            }

            return response()->json([
                'error' => 'AI service returned an error.',
                'details' => $response->json() ?? $response->body(),
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not connect to AI: ' . $e->getMessage()], 500);
        }
    }

    private function normalizeAiTestItems(array $payload): array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['items', 'predictions', 'skills', 'tests'] as $key) {
            if (!empty($payload[$key]) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return [$payload];
    }

    private function findMatchingRequestItem(array $preparedItems, array $predictionResult): ?array
    {
        $userId = $predictionResult['user_id'] ?? null;
        $skillId = $predictionResult['skill_id'] ?? null;

        if ($userId === null || $skillId === null) {
            return null;
        }

        foreach ($preparedItems as $item) {
            if (($item['user_id'] ?? null) === $userId && ($item['skill_id'] ?? null) === $skillId) {
                return $item;
            }
        }

        return null;
    }
}
