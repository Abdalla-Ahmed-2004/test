<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonAttempt extends Model
{
    /** @use HasFactory<\Database\Factories\LessonAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'lesson_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
