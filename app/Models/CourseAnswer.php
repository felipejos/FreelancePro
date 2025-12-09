<?php

namespace App\Models;

use App\Core\Model;

class CourseAnswer extends Model
{
    protected string $table = 'course_answers';

    protected array $fillable = [
        'enrollment_id',
        'module_id',
        'question_id',
        'selected_option',
        'is_correct'
    ];

    public function getByEnrollmentModule(int $enrollmentId, int $moduleId): array
    {
        $sql = "SELECT a.*, q.question_text, q.correct_option, q.explanation
                FROM {$this->table} a
                JOIN course_questions q ON a.question_id = q.id
                WHERE a.enrollment_id = :e AND a.module_id = :m
                ORDER BY q.question_number";
        return $this->query($sql, ['e' => $enrollmentId, 'm' => $moduleId]);
    }

    public function saveBatch(int $enrollmentId, int $moduleId, array $answers): bool
    {
        $this->beginTransaction();
        try {
            $this->execute(
                "DELETE FROM {$this->table} WHERE enrollment_id = :e AND module_id = :m",
                ['e' => $enrollmentId, 'm' => $moduleId]
            );
            foreach ($answers as $answer) {
                $answer['enrollment_id'] = $enrollmentId;
                $answer['module_id'] = $moduleId;
                $this->create($answer);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    public function calculateScore(int $enrollmentId, int $moduleId): float
    {
        $sql = "SELECT COUNT(*) as total, SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct
                FROM {$this->table}
                WHERE enrollment_id = :e AND module_id = :m";
        $result = $this->query($sql, ['e' => $enrollmentId, 'm' => $moduleId]);
        $total = $result[0]['total'] ?? 0;
        $correct = $result[0]['correct'] ?? 0;
        if ($total === 0) return 0;
        return round(($correct / $total) * 100, 2);
    }
}
