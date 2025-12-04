<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: PlaybookAnswer (playbook_answers)
 */
class PlaybookAnswer extends Model
{
    protected string $table = 'playbook_answers';
    
    protected array $fillable = [
        'assignment_id',
        'question_id',
        'selected_option',
        'is_correct'
    ];

    /**
     * Buscar respostas da atribuição
     */
    public function getByAssignment(int $assignmentId): array
    {
        $sql = "SELECT a.*, q.question_text, q.correct_option, q.explanation
                FROM {$this->table} a
                JOIN playbook_questions q ON a.question_id = q.id
                WHERE a.assignment_id = :assignment_id
                ORDER BY q.question_number";
        
        return $this->query($sql, ['assignment_id' => $assignmentId]);
    }

    /**
     * Salvar respostas em lote
     */
    public function saveBatch(int $assignmentId, array $answers): bool
    {
        $this->beginTransaction();
        
        try {
            // Remover respostas anteriores
            $this->execute(
                "DELETE FROM {$this->table} WHERE assignment_id = :id",
                ['id' => $assignmentId]
            );
            
            foreach ($answers as $answer) {
                $answer['assignment_id'] = $assignmentId;
                $this->create($answer);
            }
            
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Calcular nota
     */
    public function calculateScore(int $assignmentId): float
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct
                FROM {$this->table}
                WHERE assignment_id = :assignment_id";
        
        $result = $this->query($sql, ['assignment_id' => $assignmentId]);
        
        $total = $result[0]['total'] ?? 0;
        $correct = $result[0]['correct'] ?? 0;
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($correct / $total) * 100, 2);
    }
}
