<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\AiPrediction;
use App\Models\Quiz;
use App\Models\StudentSubtopicState;
use App\Models\Subtopic;
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

    public function aiTest(Request $request)
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            $payload = $request->all();
        }

        if (empty($payload)) {
            $payload = $this->buildAiTestPayload($this->resolveAiTestUserId());
        }

        try {
            if (!is_array($payload)) {
                return response()->json([
                    'error' => 'Prediction payload must be an object or array.',
                ], 400);
            }

            $items = $this->normalizeAiTestItems($payload);

            if (empty($items)) {
                return response()->json([
                    'error' => 'No valid prediction items were found.',
                ], 400);
            }

            $testPayload = [
                'items' => $items,
                'skills_difficulty' => $payload['skills_difficulty'] ?? [],
            ];

            $response = Http::acceptJson()->timeout(10)->post($this->aiPredictorUrl(), [
                'items' => $items,
                'skills_difficulty' => $payload['skills_difficulty'] ?? [],
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                return response()->json([
                    'message' => 'Connected to AI successfully!',
                    'payload' => $testPayload,
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

    private function buildAiTestPayload($userId = null): array
    {
        return [
            'items' => [
                ['order_id' => 101, 'skill_id' => 10, 'skill_name' => 'Algebra Basics', 'user_id' => $userId, 'is_correct' => 1],
                ['order_id' => 102, 'skill_id' => 10, 'skill_name' => 'Algebra Basics', 'user_id' => $userId, 'is_correct' => 0],
                ['order_id' => 103, 'skill_id' => 10, 'skill_name' => 'Algebra Basics', 'user_id' => $userId, 'is_correct' => 1],
                ['order_id' => 104, 'skill_id' => 12, 'skill_name' => 'Fractions', 'user_id' => $userId, 'is_correct' => 0],
                ['order_id' => 105, 'skill_id' => 12, 'skill_name' => 'Fractions', 'user_id' => $userId, 'is_correct' => 1],
                ['order_id' => 106, 'skill_id' => 15, 'skill_name' => 'Word Problems', 'user_id' => $userId, 'is_correct' => 1],
                ['order_id' => 107, 'skill_id' => 15, 'skill_name' => 'Word Problems', 'user_id' => $userId, 'is_correct' => 1],
                ['order_id' => 108, 'skill_id' => 15, 'skill_name' => 'Word Problems', 'user_id' => $userId, 'is_correct' => 0],
            ],
            'skills_difficulty' => [
                10 => 0.35,
                12 => 0.60,
                15 => 0.25,
            ],
        ];
    }

    private function resolveAiTestUserId()
    {
        try {
            return JWTAuth::user()?->id;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function aiPredictorUrl(): string
    {
        return env('AI_PREDICTOR_URL', 'http://127.0.0.1:5000/predict');
    }


    public function subtopicEvaluation(Quiz $quiz)
    {
        $student = JWTAuth::user()->student;
        $userId = $student->id;

        $quiz_subtopics = $quiz->questions()->pluck('subtopic_id')->unique()->toArray();
        $subtopics = Subtopic::whereIn('id', $quiz_subtopics)->get(['id', 'title', 'subtopic_difficulty'])->keyBy('id');
        $student_answers = $student->answers()
            ->whereIn('subtopic_id', $quiz_subtopics)
            ->orderBy('id')
            ->orderBy('subtopic_id')
            ->get(['id as order_id', 'subtopic_id as skill_id', 'correctness as is_correct']);

        $subtopic_difficulty = $subtopics->pluck('subtopic_difficulty', 'id')->toArray();

        $items = $student_answers->values()->map(function ($answer) use ($subtopics, $userId) {
            $subtopic = $subtopics->get($answer->skill_id);

            return [
                'order_id' => $answer->order_id,
                'skill_id' => $answer->skill_id,
                'skill_name' => $subtopic?->title,
                'user_id' => $userId,
                'is_correct' => $answer->is_correct,
            ];
        })->toArray();

        $payload = [
            'items' => $items,
            'skills_difficulty' => $subtopic_difficulty,
        ];

        if (empty($payload['items'])) {
            return response()->json([
                'message' => 'No student answers were found for this quiz.',
                'payload' => $payload,
            ], 404);
        }

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->post($this->aiPredictorUrl(), $payload);

            if ($response->successful()) {
                return response()->json([
                    'message' => 'Connected to AI successfully!',
                    // 'payload' => $payload,
                    'ai_response' => $response->json(),
                ], $response->status());
            }

            return response()->json([
                'message' => 'Student answers were prepared, but AI returned an error',
                'payload' => $payload,
                'details' => $response->json() ?? $response->body(),
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Student answers were prepared, but AI could not be reached',
                'payload' => $payload,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
