<?php

namespace App\Models;

use App\Core\Model;

class CourseQuestion extends Model
{
    protected string $table = 'course_questions';

    protected array $fillable = [
        'module_id',
        'question_number',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation'
    ];

    public function getByModule(int $moduleId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE module_id = :id ORDER BY question_number";
        return $this->query($sql, ['id' => $moduleId]);
    }

    public function deleteByModule(int $moduleId): bool
    {
        return $this->execute("DELETE FROM {$this->table} WHERE module_id = :id", ['id' => $moduleId]);
    }

    public function createBatch(int $moduleId, array $questions): bool
    {
        $this->beginTransaction();
        try {
            $this->deleteByModule($moduleId);
            foreach ($questions as $index => $q) {
                $q['module_id'] = $moduleId;
                $q['question_number'] = $index + 1;
                $this->create($q);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }
}
