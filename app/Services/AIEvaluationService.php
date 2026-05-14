<?php

namespace App\Services;

use App\Models\StudentAnswer;
use App\Models\subtopic;
use App\Models\StudentSubtopicProgress;
use App\Models\AiPrediction;
use App\Models\StudentSubtopicState;
use Illuminate\Support\Facades\Http;

class AIEvaluationService
{
    // Database-driven feature reconstruction is kept here for later use.
    // aiTest currently accepts fully populated feature rows directly.
    public function buildPredictionItem(int $userId, int $subtopicId, ?string $skillName = null): ?array
    {
        // Pull the student's answers for the target skill in chronological order.
        $skillAnswers = StudentAnswer::where('user_id', $userId)
            ->where('subtopic_id', $subtopicId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($skillAnswers->isEmpty()) {
            return null;
        }

        // Pull all student answers so user-level success rate can be computed from history.
        $userAnswers = StudentAnswer::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Use the subtopic difficulty from the database, defaulting to a neutral value.
        $subtopic = subtopic::find($subtopicId);
        $difficulty = (float) ($subtopic?->difficulty_level ?? 0.5);

        return array_merge(
            [
                'user_id' => $userId,
                'skill_id' => $subtopicId,
                'skill_name' => $skillName ?? $subtopic?->title ?? ('subtopic-' . $subtopicId),
            ],
            $this->calculateFeatureRow($skillAnswers, $userAnswers, $difficulty)
        );
    }

    // For now, keep only fully populated rows and leave database reconstruction inactive.
    public function buildPredictionItems(array $items): array
    {
        $payloads = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (!$this->hasFeatureSet($item)) {
                continue;
            }

            $payloads[] = $item;
        }

        return $payloads;
    }

    public function evaluateStudentsubtopic($userId, $subtopicId)
    {
        // Manual evaluation still keeps the database-based builder available for future use.
        $payload = $this->buildPredictionItem((int) $userId, (int) $subtopicId);

        if ($payload === null) {
            return null;
        }

        $response = Http::acceptJson()->timeout(10)->post(env('AI_PREDICTOR_URL'), [
            'items' => [$payload],
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            $aiData = $responseData['data'][0] ?? null;

            if ($aiData === null) {
                return null;
            }

            $this->updateStudentProgress($userId, $subtopicId, $aiData);

            return $aiData;
        }

        return null;
    }

    private function updateStudentProgress($userId, $subtopicId, $aiData)
    {
        $subtopic = subtopic::find($subtopicId);
        $masteryLevel = $aiData['ai_mastery_score'] ?? $aiData['prob'] ?? 0;

        if ($masteryLevel > 1) {
            $masteryLevel = $masteryLevel / 100;
        }

        StudentSubtopicProgress::updateOrCreate(
            ['student_id' => $userId, 'subtopic_tag' => $subtopic?->title ?? ('subtopic-' . $subtopicId)],
            [
                'mastery_level' => $masteryLevel,
                'last_updated' => now(),
            ]
        );

        // Save the full AI prediction features to the database.
        $this->savePredictionFeatures($userId, $subtopicId, $aiData);
    }

    private function savePredictionFeatures($userId, $subtopicId, $aiData)
    {
        // Extract all features from the AI response.
        $predictionData = [
            'user_id' => $userId,
            'subtopic_id' => $subtopicId,
            'decayed_mastery' => $aiData['decayed_mastery'] ?? 0.0,
            'skill_difficulty_avg' => $aiData['skill_difficulty_avg'] ?? 0.0,
            'student_skill_history' => $aiData['student_skill_history'] ?? 0.0,
            'user_success_rate' => $aiData['user_success_rate'] ?? 0.0,
            'weighted_streak' => $aiData['weighted_streak'] ?? 0.0,
            'consecutive_correct' => $aiData['consecutive_correct'] ?? 0,
            'opportunity_count' => $aiData['opportunity_count'] ?? 0,
            'learning_momentum' => $aiData['learning_momentum'] ?? 0.0,
            'weighted_consistency' => $aiData['weighted_consistency'] ?? 0.0,
            'total_experience_score' => $aiData['total_experience_score'] ?? 0.0,
            'complexity_gap' => $aiData['complexity_gap'] ?? 0.0,
            'mastery_history_gap' => $aiData['mastery_history_gap'] ?? 0.0,
            'performance_efficiency' => $aiData['performance_efficiency'] ?? 0.0,
            'consistency_success_sync' => $aiData['consistency_success_sync'] ?? 0.0,
            'prediction_probability' => $aiData['prob'] ?? null,
            'prediction_status' => $aiData['status'] ?? null,
        ];

        AiPrediction::updateOrCreate(
            ['user_id' => $userId, 'subtopic_id' => $subtopicId],
            $predictionData
        );

        // Also save to the new student_subtopic_state table
        StudentSubtopicState::updateOrCreate(
            ['user_id' => $userId, 'subtopic_id' => $subtopicId],
            $predictionData
        );
    }

    private function calculateFeatureRow($skillAnswers, $userAnswers, float $difficulty): array
    {
        // Convert answer history into 0/1 values for the feature equations below.
        $skillCorrectValues = $this->normalizeCorrectValues($skillAnswers);
        $userCorrectValues = $this->normalizeCorrectValues($userAnswers);

        // Core counts and history-based signals.
        $attemptsSoFar = $skillCorrectValues->count();
        $consecutiveCorrect = $this->currentStreak($skillCorrectValues);
        $decayedMastery = $this->decayedMastery($skillCorrectValues);
        $studentSkillHistory = $skillCorrectValues->avg();
        $userSuccessRate = $userCorrectValues->avg();

        if ($studentSkillHistory === null) {
            $studentSkillHistory = 1 - $difficulty;
        }

        if ($userSuccessRate === null) {
            $userSuccessRate = 0.5;
        }

        // Derived stability and momentum features mirror preprocessing.py.
        $weightedStreak = min($consecutiveCorrect * pow(2.0, $difficulty), 15.0);
        $performanceEfficiency = min(max($studentSkillHistory / ($difficulty + 0.05), 0), 15);
        $consistencySuccessSync = min(max($decayedMastery * (1 - $difficulty) * 1.5, 0), 1);
        $complexityGap = max(min($studentSkillHistory - $difficulty, 1), -1);
        $learningMomentum = max(min($decayedMastery - $studentSkillHistory, 1), -1);
        $weightedConsistency = log($consecutiveCorrect + 1, 2) * (1 - $difficulty);
        $totalExperienceScore = log(1 + $attemptsSoFar);
        $masteryHistoryGap = $decayedMastery - $studentSkillHistory;

        return [
            'decayed_mastery' => round($decayedMastery, 4),
            'skill_difficulty_avg' => round($difficulty, 4),
            'student_skill_history' => round((float) $studentSkillHistory, 4),
            'user_success_rate' => round((float) $userSuccessRate, 4),
            'weighted_streak' => round($weightedStreak, 4),
            'consecutive_correct' => $consecutiveCorrect,
            'opportunity_count' => $attemptsSoFar,
            'learning_momentum' => round($learningMomentum, 4),
            'weighted_consistency' => round($weightedConsistency, 4),
            'total_experience_score' => round($totalExperienceScore, 4),
            'complexity_gap' => round($complexityGap, 4),
            'mastery_history_gap' => round($masteryHistoryGap, 4),
            'performance_efficiency' => round($performanceEfficiency, 4),
            'consistency_success_sync' => round($consistencySuccessSync, 4),
        ];
    }

    // Convert the student's answers into a binary correctness series.
    private function normalizeCorrectValues($answers)
    {
        return $answers->map(function ($answer) {
            return (int) ((bool) ($answer->correctness ?? false));
        })->values();
    }

    // Count the number of consecutive correct answers at the end of the history.
    private function currentStreak($correctValues): int
    {
        $streak = 0;

        for ($index = $correctValues->count() - 1; $index >= 0; $index--) {
            if ((int) $correctValues[$index] !== 1) {
                break;
            }

            $streak++;
        }

        return $streak;
    }

    // Recreate the decayed mastery smoothing used by preprocessing.py.
    private function decayedMastery($correctValues, float $alpha = 0.55): float
    {
        $currentMastery = 0.5;

        foreach ($correctValues as $value) {
            $currentMastery = $currentMastery + $alpha * ((int) $value - $currentMastery);
        }

        return $currentMastery;
    }

    private function hasFeatureSet(array $item): bool
    {
        $requiredFeatures = [
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
        ];

        foreach ($requiredFeatures as $feature) {
            if (!array_key_exists($feature, $item)) {
                return false;
            }
        }

        return true;
    }
}
