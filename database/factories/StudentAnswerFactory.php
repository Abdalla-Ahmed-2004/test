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
        // Pick a random question first, then derive quiz_id from it
        $question = Question::inRandomOrder()->first();

        return [
            'quiz_id' => $question->quiz_id,
            'student_id' => Student::inRandomOrder()->value('id'),
            'question_id' => $question->id,
            'answer_text' => $this->faker->sentence(),
            'correctness' => $this->faker->boolean(),
        ];
    }
}
