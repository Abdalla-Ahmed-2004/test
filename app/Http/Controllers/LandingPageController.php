<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $data = cache()->remember('landing_page_stats', 86400, function () {
            $subjects = Subject::withCount('teachers')->get();
            return [
                'subjects' => $subjects,
                'subjects_count' => $subjects->count(),
                'all_teachers_count' => Teacher::count(),
                'all_students_count' => Student::count(),
            ];
        });

        return response()->json([
            'message' => 'Welcome to the Focus Learning Platform API. Please refer to the documentation for usage details.',
            'subjects' => $data['subjects'],
            'subjects_count' => $data['subjects_count'],
            'all_teachers_count' => $data['all_teachers_count'],
            'all_students_count' => $data['all_students_count'],
        ]);
    }
}
