<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\StudentResource;
use App\Http\Resources\TeacherResource;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use App\Models\Subject;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        if (isset($data['teacher']) && $data['teacher']) {
            $user->assignRole('teacher');
            $user->teacher()->create([
                'subject_id' => $data['subject_id'],
            ]);
        }
        if (isset($data['student']) && $data['student']) {
            $user->assignRole('student');
            $user->student()->create();
        }
        $token = JWTAuth::fromUser($user);

        return $user->hasRole('teacher') ?
            (new TeacherResource($user->teacher))->additional(['token' => $token])->response()->setStatusCode(201) : (new StudentResource($user->student))->additional(['token' => $token])->response()->setStatusCode(201);
        // dd(subject::all())  ;

    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
       
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    if (JWTAuth::user()->hasRole('teacher')) {
            $teacher = JWTAuth::user()->teacher;
            $points = QuizAttempt::whereHas('quiz', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->select('score')->avg('score');
        }
        return response()->json([

            'user' => JWTAuth::user()->hasRole('teacher') ?
                (new TeacherResource(JWTAuth::user()->teacher))->additional(['videos_count' => JWTAuth::user()->teacher->videos()->count(), 'quizzes_count' => JWTAuth::user()->teacher->quizzes()->count(), 'average_score' => $points])->response()->getData(true) :
                new StudentResource(JWTAuth::user()->student),
            'token' => $token,
        ], 201);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me()
    {
        // dd(JWTAuth::user());
        return response()->json(JWTAuth::user());
    }

    public function updateProfile(\App\Http\Requests\UpdateUserRequest $request)
    {
        $user = JWTAuth::user();
        $user->update($request->validated());

        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }
}
