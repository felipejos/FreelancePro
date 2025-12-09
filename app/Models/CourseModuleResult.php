<?php

namespace App\Models;

use App\Core\Model;

class CourseModuleResult extends Model
{
    protected string $table = 'course_module_results';

    protected array $fillable = [
        'enrollment_id',
        'module_id',
        'attempts',
        'score',
        'passed',
        'locked',
        'last_attempt_at'
    ];

    public function getResult(int $enrollmentId, int $moduleId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE enrollment_id = :e AND module_id = :m LIMIT 1";
        $rows = $this->query($sql, ['e' => $enrollmentId, 'm' => $moduleId]);
        return $rows[0] ?? null;
    }

    public function upsertAttempt(int $enrollmentId, int $moduleId, float $score, bool $passed, bool $lock): bool
    {
        $existing = $this->getResult($enrollmentId, $moduleId);
        if ($existing) {
            $sql = "UPDATE {$this->table} SET attempts = attempts + 1, score = :s, passed = :p, locked = :l, last_attempt_at = NOW() WHERE id = :id";
            return $this->execute($sql, ['s' => $score, 'p' => $passed ? 1 : 0, 'l' => $lock ? 1 : 0, 'id' => $existing['id']]);
        }
        return (bool) $this->create([
            'enrollment_id' => $enrollmentId,
            'module_id' => $moduleId,
            'attempts' => 1,
            'score' => $score,
            'passed' => $passed ? 1 : 0,
            'locked' => $lock ? 1 : 0,
            'last_attempt_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
