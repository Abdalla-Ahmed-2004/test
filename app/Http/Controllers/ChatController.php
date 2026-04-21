<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendChatMessageRequest;
use App\Http\Resources\ChatResource;
use App\Models\Lesson;
use App\Services\GeminiService;
use App\Services\LessonContextBuilder;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChatController extends Controller
{
    protected GeminiService $geminiService;
    protected LessonContextBuilder $contextBuilder;

    public function __construct(
        GeminiService $geminiService,
        LessonContextBuilder $contextBuilder
    ) {
        $this->geminiService = $geminiService;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * Send a chat message and get AI response
     *
     * @param SendChatMessageRequest $request
     * @return ChatResource|Response
     */
    public function send(SendChatMessageRequest $request)
    {
        try {
            $validated = $request->validated();
            $message = $validated['message'];
            $lessonId = $validated['lesson_id'] ?? null;

            $systemContext = null;
            $lessonTitle = null;

            // Build context if lesson_id is provided
            if ($lessonId) {
                $lesson = Lesson::findOrFail($lessonId);
                $systemContext = $this->contextBuilder->getSystemPrompt($lesson);
                $lessonTitle = $lesson->title;
            }

            // Get AI response
            $response = $this->geminiService->chat($message, $systemContext);

            // Prepare response data
            $data = [
                'user_message' => $message,
                'ai_response' => $response,
                'lesson_id' => $lessonId,
                'lesson_title' => $lessonTitle,
                'timestamp' => now()->toIso8601String(),
            ];

            return response()->json(new ChatResource($data), Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Chat Controller Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to process chat message',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get current chat session (placeholder for session management)
     *
     * @return Response
     */
    public function getSession()
    {
        $user = JWTAuth::user();

        return response()->json([
            'message' => 'Chat session active',
            'user_id' => $user->id,
            'session_start' => now()->toIso8601String(),
        ], Response::HTTP_OK);
    }
}
