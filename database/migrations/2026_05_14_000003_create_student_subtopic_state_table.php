<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_subtopic_state', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subtopic_id');

            // AI Prediction Features (14 features from the model)
            $table->decimal('decayed_mastery', 5, 4)->default(0.5000)->comment('Exponential moving average of mastery');
            $table->decimal('skill_difficulty_avg', 5, 4)->default(0.5000)->comment('Average difficulty level');
            $table->decimal('student_skill_history', 5, 4)->default(0.5000)->comment('Expanding mean of correctness');
            $table->decimal('user_success_rate', 5, 4)->default(0.5000)->comment('All-time student success rate');
            $table->decimal('weighted_streak', 8, 4)->default(0.0000)->comment('Streak multiplied by difficulty power');
            $table->integer('consecutive_correct')->default(0)->comment('Current correct streak count');
            $table->integer('opportunity_count')->default(0)->comment('Total attempts on skill');
            $table->decimal('learning_momentum', 5, 4)->default(0.0000)->comment('Mastery gap, clipped [-1,1]');
            $table->decimal('weighted_consistency', 8, 4)->default(0.0000)->comment('log2(correct+1) * (1-difficulty)');
            $table->decimal('total_experience_score', 8, 4)->default(0.0000)->comment('log(1+attempts)');
            $table->decimal('complexity_gap', 5, 4)->default(0.0000)->comment('History - difficulty, clipped [-1,1]');
            $table->decimal('mastery_history_gap', 5, 4)->default(0.0000)->comment('Decay - history');
            $table->decimal('performance_efficiency', 8, 4)->default(0.0000)->comment('History/(difficulty+0.05), clipped [0,15]');
            $table->decimal('consistency_success_sync', 5, 4)->default(0.0000)->comment('Decay*(1-difficulty)*1.5, clipped [0,1]');

            // Subtopic Evaluation
            $table->decimal('prediction_probability', 5, 2)->nullable()->comment('AI prediction probability (0-100)');
            $table->string('prediction_status')->nullable()->comment('Strong Skill, Developing, or Weak Skill');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('subtopic_id')->references('id')->on('subtopics')->onDelete('cascade');

            // Unique constraint: one state per user-subtopic pair
            $table->unique(['user_id', 'subtopic_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subtopic_state');
    }
};
