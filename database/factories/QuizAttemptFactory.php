<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::inRandomOrder()->value('id'),
            'quiz_id' => Quiz::inRandomOrder()->value('id'),
            'score' => $this->faker->numberBetween(0, 100),
        ];
    }
}
