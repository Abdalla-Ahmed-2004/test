<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreteacherRequest;
use App\Http\Requests\UpdateteacherRequest;
use App\Http\Resources\quizResource;
use App\Http\Resources\TeacherCollection;
use App\Http\Resources\teacherResource;
use App\Http\Resources\VideoCollection;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Video;
use Tymon\JWTAuth\Facades\JWTAuth;

class TeacherController extends Controller
{
    public function test(Subject $subject, Teacher $teacher, Video $video)
    {
        return '54';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Subject $subject)
    {
        $page = request()->get('page', 1);
        $cacheKey = 'teachers_subject_' . $subject->id . '_page_' . $page;

        $teachers = cache()->remember($cacheKey, 60, function () use ($subject) {
            return $subject->teachers()->paginate(10);
        });

        // Use response()->json() with custom data structure while preserving pagination via getData(true)
        return response()->json([
            'teachers' => (new TeacherCollection($teachers))->response()->getData(true)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreteacherRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        $lessons = $teacher->lessons()
            ->select('lessons.id', 'lessons.title')
            ->distinct()
            ->paginate(10)
            ->through(function ($lesson) {
                return $lesson->makeHidden('laravel_through_key');
            });

        return ['teacher' => new TeacherResource($teacher), 'lessons' => $lessons];
    }

    public function showContent(Teacher $teacher, Lesson $lesson)
    {
        $videos = $teacher->videos()->where('lesson_id', $lesson->id)->paginate(10);

        return response()->json([
            'teacher' => new TeacherResource($teacher),
            'videos' => (new VideoCollection($videos))->response()->getData(true)
        ]);
    }

    public function showQuiz(Quiz $quiz)
    {
        // dd($teacher->videos->quiz);
        $student = JWTAuth::user()->student;
        $teacher = $quiz->teacher;
        // dd($student->quizzesAttempt->where('quiz_id', $quiz->id)->first());
        if ($student->quizzesAttempt->where('quiz_id', $quiz->id)->first()) {
            return response()->json([
                'message' => 'you attempt this quiz ',
            ]);
        }

        return ['teacher' => new TeacherResource($teacher), 'quiz' => new QuizResource($quiz)];
        // return (new teacherResource($teacher))->additional([ 'quiz'=> new quizResource($quiz)]);
        // return (new teacherResource($teacher))->additional([ 'lessons'=> $teacher->videos->load('lesson:id,title')->pluck('lesson')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        //
    }
}
