<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\VideoCollection;
use App\Models\Video;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teacher = JWTAuth::user()->teacher;
        $cacheKey = 'videos_teacher_'.$teacher->id;

        // Paginated version
        // $videos = cache()->remember($cacheKey, 1440, function () use ($teacher) {
        //     return $teacher->videos()->paginate(10);
        // });

        // Non-paginated version
        $videos = cache()->remember($cacheKey.'_all', 60, function () use ($teacher) {
            return $teacher->videos;
        });

        return ['teacher' => new teacherResource($teacher), 'videos' => new VideoCollection($videos)];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVideoRequest $request)
    {
        Gate::authorize('create', Video::class);
        $teacher = JWTAuth::user()->teacher;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storeAs('videos', uniqid().'_'.$file->getClientOriginalName(), 'public');
            $video = Video::create([
                'teacher_id' => $teacher->id,
                'lesson_id' => $request->input('lesson_id'),
                'title' => $request->input('title'),
                'url' => $path,
            ]);

            return response()->json($video, 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Video $video)
    {
        return response()->json($video);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVideoRequest $request, Video $video)
    {
        Gate::authorize('update', [Video::class, $video]);
        $video->update($request->validated());

        return response()->json($video);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        Gate::authorize('delete', [Video::class, $video]);
        Storage::disk('public')->delete($video->url);
        $video->delete();

        return response()->json(['message' => 'Video deleted successfully']);
    }
}
