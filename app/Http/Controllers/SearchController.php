<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuizCollection;
use App\Http\Resources\LessonCollection;
use App\Http\Resources\TeacherCollection;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\Subtopic;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $search_statement = $request->query('statement', '');
        $page = clone $request->query(); // To safely capture the page
        $pageNumber = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);

        $cacheKey = 'search:combined:' . md5($search_statement) . ':' . $pageNumber . ':' . $perPage;

        $paginator = Cache::remember($cacheKey, 1800, function () use ($search_statement, $pageNumber, $perPage, $request) {
            $teachers = Teacher::whereHas('user', function ($query) use ($search_statement) {
                $query->where('name', 'like', '%' . $search_statement . '%');
            })->get();

            $lessons = Lesson::select(
                'lessons.id',
                'lessons.title',
                'teachers.id as teacher_id',
                'users.name as teacher_name',
                'users.profile_picture as teacher_profile_picture',
                \DB::raw("'lesson' as search_type")
            )
                ->join('videos', 'lessons.id', '=', 'videos.lesson_id')
                ->join('teachers', 'videos.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('lessons.title', 'like', '%' . $search_statement . '%')
                ->distinct()
                ->get();

            $subtopics = Subtopic::select(
                'subtopics.id',
                'subtopics.title',
                'lessons.id as lesson_id',
                'lessons.title as lesson_title',
                'teachers.id as teacher_id',
                'users.name as teacher_name',
                'users.profile_picture as teacher_profile_picture',
                \DB::raw("'subtopic' as search_type")
            )
                ->join('lessons', 'subtopics.lesson_id', '=', 'lessons.id')
                ->join('videos', 'lessons.id', '=', 'videos.lesson_id')
                ->join('teachers', 'videos.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('subtopics.title', 'like', '%' . $search_statement . '%')
                ->distinct()
                ->get();

            $formattedTeachers = collect((new TeacherCollection($teachers))->resolve())->map(function ($item) {
                $itemArray = (array) $item;
                $itemArray['search_type'] = 'teacher';
                return $itemArray;
            });

            $combined = $formattedTeachers->concat($lessons)->concat($subtopics);

            $paginatedItems = $combined->slice(($pageNumber - 1) * $perPage, $perPage)->values();

            return new LengthAwarePaginator(
                $paginatedItems,
                $combined->count(),
                $perPage,
                $pageNumber,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        });

        return response()->json($paginator);
    }
}
