<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentSubtopicState extends Model
{
    use SoftDeletes;

    protected $table = 'student_subtopic_state';

    protected $fillable = [
        'user_id',
        'subtopic_id',
        'decayed_mastery',
        'skill_difficulty_avg',
        'student_skill_history',
        'user_success_rate',
        'weighted_streak',
        'consecutive_correct',
        'opportunity_count',
        'learning_momentum',
        'weighted_consistency',
        'total_experience_score',
        'complexity_gap',
        'mastery_history_gap',
        'performance_efficiency',
        'consistency_success_sync',
        'prediction_probability',
        'prediction_status',
    ];

    protected $casts = [
        'decayed_mastery' => 'decimal:4',
        'skill_difficulty_avg' => 'decimal:4',
        'student_skill_history' => 'decimal:4',
        'user_success_rate' => 'decimal:4',
        'weighted_streak' => 'decimal:4',
        'consecutive_correct' => 'integer',
        'opportunity_count' => 'integer',
        'learning_momentum' => 'decimal:4',
        'weighted_consistency' => 'decimal:4',
        'total_experience_score' => 'decimal:4',
        'complexity_gap' => 'decimal:4',
        'mastery_history_gap' => 'decimal:4',
        'performance_efficiency' => 'decimal:4',
        'consistency_success_sync' => 'decimal:4',
        'prediction_probability' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user associated with this state.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subtopic associated with this state.
     */
    public function subtopic()
    {
        return $this->belongsTo(subtopic::class);
    }

    /**
     * Scope: Get states for a specific user and subtopic.
     */
    public function scopeForUserSubtopic($query, $userId, $subtopicId)
    {
        return $query->where('user_id', $userId)->where('subtopic_id', $subtopicId);
    }

    /**
     * Scope: Get strong skill states.
     */
    public function scopeStrongSkills($query)
    {
        return $query->where('prediction_status', 'Strong Skill');
    }

    /**
     * Scope: Get developing skill states.
     */
    public function scopeDevelopingSkills($query)
    {
        return $query->where('prediction_status', 'Developing');
    }

    /**
     * Scope: Get weak skill states.
     */
    public function scopeWeakSkills($query)
    {
        return $query->where('prediction_status', 'Weak Skill');
    }

    /**
     * Get all 14 features as array.
     */
    public function getFeatures()
    {
        return [
            'decayed_mastery' => $this->decayed_mastery,
            'skill_difficulty_avg' => $this->skill_difficulty_avg,
            'student_skill_history' => $this->student_skill_history,
            'user_success_rate' => $this->user_success_rate,
            'weighted_streak' => $this->weighted_streak,
            'consecutive_correct' => $this->consecutive_correct,
            'opportunity_count' => $this->opportunity_count,
            'learning_momentum' => $this->learning_momentum,
            'weighted_consistency' => $this->weighted_consistency,
            'total_experience_score' => $this->total_experience_score,
            'complexity_gap' => $this->complexity_gap,
            'mastery_history_gap' => $this->mastery_history_gap,
            'performance_efficiency' => $this->performance_efficiency,
            'consistency_success_sync' => $this->consistency_success_sync,
        ];
    }
}
