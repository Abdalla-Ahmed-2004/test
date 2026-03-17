<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teacher = Teacher::inRandomOrder()->first();

        // Pick a lesson from the teacher's subject
        $lessonId = Lesson::whereHas('unit', function ($q) use ($teacher) {
            $q->where('subject_id', $teacher->subject_id);
        })->inRandomOrder()->value('id');

        // Pick a video linked to a lesson in the teacher's subject
        $videoId = Video::whereHas('lesson.unit', function ($q) use ($teacher) {
            $q->where('subject_id', $teacher->subject_id);
        })->inRandomOrder()->value('id');

        return [
            'lesson_id' => $lessonId,
            'teacher_id' => $teacher->id,
            'video_id' => $videoId,
        ];
    }
}
