<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Tymon\JWTAuth\Facades\JWTAuth;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $student = JWTAuth::user()->student;
        $lesson_attempts = $student->lessonAttempts()->where('quiz_attempted', true)->orderBy('created_at', 'desc')->get();

         return response()->json([
            'message' => 'Student dashboard data retrieved successfully',
            'student' => new StudentResource($student),
            'lesson_attempts_completed_count' => $lesson_attempts->count(),
                'lesson_attempts' => $student->lessonAttempts->map(function ($attempt) use($student) {
                $score = $attempt->quiz_id ? $student->quizzesAttempt()->where('quiz_id', $attempt->quiz_id)->value('score') : null;
                $total_marks = $attempt->quiz_id ? $attempt->quiz->total_marks : null;
                return [
                        'lesson_title' => $attempt->lesson->title,
                        'video_title' => $attempt->video->title,
                        'quiz_title' => $attempt->quiz ? $attempt->quiz->title : null,
                        'score' => $score,
                        'total_marks' => $attempt->quiz_id ? $attempt->quiz->total_marks : null,
                        'percentage' => $attempt->quiz_id && $total_marks ? round(($score / $total_marks) * 100, 2) : null,
                        'attempted_at' => $attempt->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
             // You can add more data here as needed, such as recent quiz attempts, recommended lessons, etc.
         ]);
        // For now, just return a placeholder response
     
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        //
    }
}
