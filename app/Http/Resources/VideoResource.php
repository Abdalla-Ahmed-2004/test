<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'lesson_id' => $this->lesson->id,
            'lesson_title' => $this->lesson->title,
            'video_id' => $this->id,
            'video_title' => $this->title,
            'video_url' => $this->url,
            'created_at' => $this->created_at->format('Y-m-d h:i:s'),
            // 'quizzes'=>new quizCollection($this->quizzes)
            'quizzes_count' => $this->quizzes->count(),
            'quizzes' => $this->quizzes->pluck('id')->toArray(),
        ];
    }
}
