<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: CourseLesson (course_lessons)
 */
class CourseLesson extends Model
{
    protected string $table = 'course_lessons';
    
    protected array $fillable = [
        'module_id',
        'title',
        'content_html',
        'video_url',
        'order_number',
        'duration_minutes'
    ];

    /**
     * Buscar aulas do módulo
     */
    public function getByModule(int $moduleId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE module_id = :id ORDER BY order_number";
        return $this->query($sql, ['id' => $moduleId]);
    }

    /**
     * Buscar aulas do curso
     */
    public function getByCourse(int $courseId): array
    {
        $sql = "SELECT l.*, m.title as module_title
                FROM {$this->table} l
                JOIN course_modules m ON l.module_id = m.id
                WHERE m.course_id = :id
                ORDER BY m.order_number, l.order_number";
        
        return $this->query($sql, ['id' => $courseId]);
    }

    /**
     * Obter próximo número de ordem
     */
    public function getNextOrder(int $moduleId): int
    {
        $sql = "SELECT MAX(order_number) as max_order FROM {$this->table} WHERE module_id = :id";
        $result = $this->query($sql, ['id' => $moduleId]);
        return ($result[0]['max_order'] ?? 0) + 1;
    }

    /**
     * Buscar próxima aula
     */
    public function getNextLesson(int $currentLessonId): ?array
    {
        $current = $this->find($currentLessonId);
        
        if (!$current) {
            return null;
        }
        
        // Tentar próxima aula do mesmo módulo
        $sql = "SELECT * FROM {$this->table} 
                WHERE module_id = :module_id AND order_number > :order_number 
                ORDER BY order_number LIMIT 1";
        
        $result = $this->query($sql, [
            'module_id' => $current['module_id'],
            'order_number' => $current['order_number']
        ]);
        
        if (!empty($result)) {
            return $result[0];
        }
        
        // Tentar primeira aula do próximo módulo
        $sql = "SELECT l.* FROM {$this->table} l
                JOIN course_modules m ON l.module_id = m.id
                JOIN course_modules cm ON cm.id = :current_module_id
                WHERE m.course_id = cm.course_id AND m.order_number > cm.order_number
                ORDER BY m.order_number, l.order_number
                LIMIT 1";
        
        $result = $this->query($sql, ['current_module_id' => $current['module_id']]);
        
        return $result[0] ?? null;
    }
}
