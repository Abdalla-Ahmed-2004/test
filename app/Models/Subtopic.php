<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtopic extends Model
{
    /** @use HasFactory<\Database\Factories\SubtopicFactory> */
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',

    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
