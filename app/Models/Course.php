<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: Course (courses)
 */
class Course extends Model
{
    protected string $table = 'courses';
    
    protected array $fillable = [
        'company_id',
        'title',
        'description',
        'thumbnail',
        'status',
        'is_paid',
        'total_modules',
        'total_lessons',
        'estimated_hours'
    ];

    /**
     * Buscar cursos da empresa
     */
    public function getByCompany(int $companyId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE company_id = :company_id ORDER BY created_at DESC";
        return $this->query($sql, ['company_id' => $companyId]);
    }

    /**
     * Buscar cursos publicados
     */
    public function getPublishedByCompany(int $companyId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE company_id = :company_id AND status = 'published' 
                ORDER BY created_at DESC";
        return $this->query($sql, ['company_id' => $companyId]);
    }

    /**
     * Contar cursos da empresa
     */
    public function countByCompany(int $companyId): int
    {
        return $this->countWhere('company_id', $companyId);
    }

    /**
     * Buscar curso completo com módulos e aulas
     */
    public function getComplete(int $courseId): ?array
    {
        $course = $this->find($courseId);
        
        if (!$course) {
            return null;
        }
        
        // Buscar módulos
        $sql = "SELECT * FROM course_modules WHERE course_id = :id ORDER BY order_number";
        $modules = $this->query($sql, ['id' => $courseId]);
        
        // Buscar aulas e questões de cada módulo
        foreach ($modules as &$module) {
            $sql = "SELECT * FROM course_lessons WHERE module_id = :id ORDER BY order_number";
            $module['lessons'] = $this->query($sql, ['id' => $module['id']]);
            
            $sql = "SELECT * FROM course_questions WHERE module_id = :id ORDER BY question_number";
            $module['questions'] = $this->query($sql, ['id' => $module['id']]);
        }
        
        $course['modules'] = $modules;
        return $course;
    }

    /**
     * Atualizar contadores
     */
    public function updateCounters(int $courseId): bool
    {
        $sql = "UPDATE {$this->table} SET 
                total_modules = (SELECT COUNT(*) FROM course_modules WHERE course_id = :id1),
                total_lessons = (SELECT COUNT(*) FROM course_lessons l 
                                 JOIN course_modules m ON l.module_id = m.id 
                                 WHERE m.course_id = :id2)
                WHERE id = :id3";
        
        return $this->execute($sql, ['id1' => $courseId, 'id2' => $courseId, 'id3' => $courseId]);
    }
}
