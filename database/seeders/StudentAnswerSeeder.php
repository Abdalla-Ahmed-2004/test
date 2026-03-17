<?php

namespace Database\Seeders;

use App\Models\StudentAnswer;
use Illuminate\Database\Seeder;

class StudentAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudentAnswer::factory(50)->create();
    }
}
