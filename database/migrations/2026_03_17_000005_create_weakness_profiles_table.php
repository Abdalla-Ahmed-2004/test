<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weakness_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('subtopic_tag'); // e.g. "Algebra-Quadratic"
            $table->unsignedTinyInteger('mastery_level')->default(0); // 0–100
            $table->timestamp('last_updated')->useCurrent();

            // A student can have one profile per subtopic
            $table->unique(['student_id', 'subtopic_tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weakness_profiles');
    }
};
