<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tymon\JWTAuth\Facades\JWTAuth;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'quiz_attempt'=>JWTAuth::user()->student->quizzesAttempt()->where('quiz_id', $this->id)->first()? true:false,
            'quiz_id' => $this->id,
            'lesson_id' => $this->lesson->id,
            'lesson_name' => $this->lesson->title,
            'created_at' => $this->created_at->format('Y-m-d h:i:s'),
            'questions' => new QuestionCollection($this->questions),
        ];
    }
}
