<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentAnswer>
 */
class StudentAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => function () {
                // Ensure we get a quiz attempt to link to a valid student/quiz combo
                return \App\Models\QuizAttempt::inRandomOrder()->value('quiz_id') ?? \App\Models\QuizAttempt::factory()->create()->quiz_id;
            },
            'student_id' => function (array $attributes) {
                // Return the student_id that corresponds to this exact attempt on this quiz
                return \App\Models\QuizAttempt::where('quiz_id', $attributes['quiz_id'])->inRandomOrder()->value('student_id');
            },
            'question_id' => function (array $attributes) {
                // Return a question that belongs to the same quiz
                $question = \App\Models\Question::where('quiz_id', $attributes['quiz_id'])->inRandomOrder()->first();
                if (!$question) {
                    $question = \App\Models\Question::factory()->create(['quiz_id' => $attributes['quiz_id']]);
                }
                return $question->id;
            },
            'answer_text' => $this->faker->sentence(),
            'correctness' => $this->faker->boolean(),
        ];
    }
}
