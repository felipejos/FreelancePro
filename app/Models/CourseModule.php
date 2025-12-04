<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: CourseModule (course_modules)
 */
class CourseModule extends Model
{
    protected string $table = 'course_modules';
    
    protected array $fillable = [
        'course_id',
        'title',
        'description',
        'order_number'
    ];

    /**
     * Buscar módulos do curso
     */
    public function getByCourse(int $courseId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE course_id = :id ORDER BY order_number";
        return $this->query($sql, ['id' => $courseId]);
    }

    /**
     * Buscar módulo com aulas
     */
    public function getWithLessons(int $moduleId): ?array
    {
        $module = $this->find($moduleId);
        
        if ($module) {
            $sql = "SELECT * FROM course_lessons WHERE module_id = :id ORDER BY order_number";
            $module['lessons'] = $this->query($sql, ['id' => $moduleId]);
        }
        
        return $module;
    }

    /**
     * Obter próximo número de ordem
     */
    public function getNextOrder(int $courseId): int
    {
        $sql = "SELECT MAX(order_number) as max_order FROM {$this->table} WHERE course_id = :id";
        $result = $this->query($sql, ['id' => $courseId]);
        return ($result[0]['max_order'] ?? 0) + 1;
    }
}
