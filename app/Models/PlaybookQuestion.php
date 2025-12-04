<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: PlaybookQuestion (playbook_questions)
 */
class PlaybookQuestion extends Model
{
    protected string $table = 'playbook_questions';
    
    protected array $fillable = [
        'playbook_id',
        'question_number',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation'
    ];

    /**
     * Buscar questões do playbook
     */
    public function getByPlaybook(int $playbookId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE playbook_id = :id ORDER BY question_number";
        return $this->query($sql, ['id' => $playbookId]);
    }

    /**
     * Contar questões do playbook
     */
    public function countByPlaybook(int $playbookId): int
    {
        return $this->countWhere('playbook_id', $playbookId);
    }

    /**
     * Criar múltiplas questões
     */
    public function createBatch(int $playbookId, array $questions): bool
    {
        $this->beginTransaction();
        
        try {
            foreach ($questions as $index => $question) {
                $question['playbook_id'] = $playbookId;
                $question['question_number'] = $index + 1;
                $this->create($question);
            }
            
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Deletar todas as questões de um playbook
     */
    public function deleteByPlaybook(int $playbookId): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE playbook_id = :id",
            ['id' => $playbookId]
        );
    }
}
