<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\Subtopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quiz = Quiz::inRandomOrder()->first();

        // Scope subtopic to the quiz's lesson
        $subtopicId = Subtopic::where('lesson_id', $quiz->lesson_id)
            ->inRandomOrder()
            ->value('id');

        $options = [
            $this->faker->word(),
            $this->faker->word(),
            $this->faker->word(),
            $this->faker->word(),
        ];

        return [
            'quiz_id' => $quiz->id,
            'subtopic_id' => $subtopicId,
            'question' => $this->faker->sentence(),
            'option_1' => $options[0],
            'option_2' => $options[1],
            'option_3' => $options[2],
            'option_4' => $options[3],
            'correct_answer' => $this->faker->randomElement($options),
        ];
    }
}
