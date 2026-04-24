<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonAttemptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'lesson_title' => $this->lesson->title,
            'student_id' => $this->student_id,
            'student_name' => $this->student->user->name,
            'student_email' => $this->student->user->email,
            // 'score' => $this->score,
            // 'attempted_at' => $this->created_at,
        ];
    }
}
