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
        $cacheKey = 'teachers_subject_'.$subject->id;

        // Paginated version
        // $teachers = cache()->remember($cacheKey, 1440, function () use ($subject) {
        //     return $subject->teachers()->paginate(10);
        // });

        // Non-paginated version
        $teachers = cache()->remember($cacheKey.'_all', 60, function () use ($subject) {
            return new TeacherCollection($subject->teachers);
        });

        return ['teachers' => $teachers];
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
        // return (new teacherResource($teacher))->additional([ 'videos'=> new videoCollection($teacher->videos)]);
        // return ['teacher' => new TeacherResource($teacher), 'lessons' => $teacher->videos()->with('lesson:id,title')->get()->pluck('lesson')];
        return ['teacher' => new teacherResource($teacher), 'lessons' => $teacher->lessons()->select('lessons.id', 'lessons.title')->distinct()->get()->makeHidden('laravel_through_key')];
        // return ['teacher' => new teacherResource($teacher), 'lessons' => $teacher->lessons->makeHidden('laravel_through_key')];
        // return (new teacherResource($teacher))->additional([ 'lessons'=> $teacher->videos->load('lesson:id,title')->pluck('lesson')]);
    }

    public function showContent(Teacher $teacher, Lesson $lesson)
    {
        // return $teacher->videos()->where('lesson_id', $lesson->id)->get();
        $videos = $teacher->videos()->where('lesson_id', $lesson->id)->get();

        // $teacher->where("name", "like","d");
        // $teacher->orderBy('score * id');
        return ['teacher' => new TeacherResource($teacher), 'videos' => new VideoCollection($videos)];
        // return (new teacherResource($teacher))->additional([ 'videos'=> new videoCollection($teacher->videos)]);
        // return (new teacherResource($teacher))->additional([ 'lessons'=> $teacher->videos->load('lesson:id,title')->pluck('lesson')]);
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
