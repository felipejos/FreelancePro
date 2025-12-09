<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Playbook;
use App\Models\PlaybookAssignment;
use App\Models\PlaybookAnswer;
use App\Models\PlaybookQuestion;
use App\Models\CourseEnrollment;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\CourseQuestion;
use App\Models\CourseAnswer;
use App\Models\CourseModuleResult;
use App\Models\CompanySetting;

/**
 * EmployeePanelController - Área do Funcionário
 */
class EmployeePanelController extends Controller
{
    /**
     * Dashboard do funcionário
     */
    public function dashboard(): void
    {
        $user = $this->currentUser();

        // Buscar treinamentos pendentes
        $assignmentModel = new PlaybookAssignment();
        $assignments = $assignmentModel->getByEmployee($user['id']);

        $pending = array_filter($assignments, fn($a) => $a['status'] !== 'completed');
        $completed = array_filter($assignments, fn($a) => $a['status'] === 'completed');

        // Buscar cursos matriculados
        $enrollmentModel = new CourseEnrollment();
        $enrollments = $enrollmentModel->getByEmployee($user['id']);

        $this->setLayout('employee');
        $this->view('employee/dashboard', [
            'title' => 'Meu Painel',
            'pendingTrainings' => $pending,
            'completedTrainings' => $completed,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Listar treinamentos do funcionário
     */
    public function trainings(): void
    {
        $user = $this->currentUser();

        $assignmentModel = new PlaybookAssignment();
        $assignments = $assignmentModel->getByEmployee($user['id']);

        $this->setLayout('employee');
        $this->view('employee/trainings', [
            'title' => 'Meus Treinamentos',
            'assignments' => $assignments,
        ]);
    }

    /**
     * Visualizar treinamento
     */
    public function viewTraining(int $assignmentId): void
    {
        $user = $this->currentUser();

        $assignmentModel = new PlaybookAssignment();
        $assignment = $assignmentModel->find($assignmentId);

        if (!$assignment || $assignment['employee_id'] != $user['id']) {
            $this->flash('error', 'Treinamento não encontrado.');
            $this->redirect('employee/trainings');
        }

        // Buscar playbook com questões
        $playbookModel = new Playbook();
        $playbook = $playbookModel->getWithQuestions($assignment['playbook_id']);

        // Verificar bloqueio por tentativas (config por empresa)
        $settings = new CompanySetting();
        $maxAttemptsPlaybook = (int) ($settings->get((int)$playbook['company_id'], 'max_attempts_playbook', 3));
        $locked = ((int)($assignment['attempts'] ?? 0) >= $maxAttemptsPlaybook) && (int)($assignment['passed'] ?? 0) !== 1;

        // Marcar como iniciado se pendente
        if ($assignment['status'] === 'pending') {
            $assignmentModel->start($assignmentId);
            $assignment['status'] = 'in_progress';
        }

        // Buscar respostas anteriores se houver
        $answerModel = new PlaybookAnswer();
        $previousAnswers = $answerModel->getByAssignment($assignmentId);

        $this->setLayout('employee');
        $this->view('employee/training-detail', [
            'title' => $playbook['title'],
            'playbook' => $playbook,
            'assignment' => $assignment,
            'previousAnswers' => $previousAnswers,
            'maxAttempts' => $maxAttemptsPlaybook,
            'locked' => $locked,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Submeter respostas do treinamento
     */
    public function submitTraining(int $assignmentId): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
        }

        $user = $this->currentUser();

        $assignmentModel = new PlaybookAssignment();
        $assignment = $assignmentModel->find($assignmentId);

        if (!$assignment || $assignment['employee_id'] != $user['id']) {
            $this->json(['error' => 'Treinamento não encontrado'], 404);
        }

        // Limite de tentativas para playbooks
        $playbookModel = new Playbook();
        $playbook = $playbookModel->find($assignment['playbook_id']);
        $settings = new CompanySetting();
        $maxAttemptsPlaybook = (int) ($settings->get((int)$playbook['company_id'], 'max_attempts_playbook', 3));
        $attemptsUsed = (int) ($assignment['attempts'] ?? 0);
        if ($attemptsUsed >= $maxAttemptsPlaybook && (int)($assignment['passed'] ?? 0) !== 1) {
            $this->json(['error' => 'Limite de tentativas atingido. Solicite liberação ao gestor.', 'locked' => true], 403);
        }

        $answers = $this->input('answers', []);

        if (empty($answers)) {
            $this->json(['error' => 'Responda todas as questões'], 400);
        }

        // Buscar questões para verificar respostas
        $questionModel = new PlaybookQuestion();
        $questions = $questionModel->getByPlaybook($assignment['playbook_id']);

        // Processar respostas
        $processedAnswers = [];
        foreach ($questions as $question) {
            $selectedOption = $answers[$question['id']] ?? null;
            
            if ($selectedOption) {
                $isCorrect = strtoupper($selectedOption) === strtoupper($question['correct_option']);
                $processedAnswers[] = [
                    'question_id' => $question['id'],
                    'selected_option' => $selectedOption,
                    'is_correct' => $isCorrect,
                ];
            }
        }

        // Salvar respostas
        $answerModel = new PlaybookAnswer();
        $answerModel->saveBatch($assignmentId, $processedAnswers);

        // Calcular nota
        $score = $answerModel->calculateScore($assignmentId);
        $passed = $score >= 70; // 70% para aprovação

        // Registrar tentativa mantendo status adequado
        $assignmentModel->recordAttempt($assignmentId, $score, $passed);

        $attemptsAfter = $attemptsUsed + 1;
        $locked = (!$passed && $attemptsAfter >= $maxAttemptsPlaybook);

        $this->json([
            'success' => true,
            'score' => $score,
            'passed' => $passed,
            'attempts' => $attemptsAfter,
            'max_attempts' => $maxAttemptsPlaybook,
            'locked' => $locked,
            'message' => $passed 
                ? "Parabéns! Você foi aprovado com {$score}%!" 
                : ($locked ? "Você obteve {$score}%. Limite de tentativas atingido. Solicite liberação ao gestor." : "Você obteve {$score}%. Nota mínima: 70%. Tente novamente."),
        ]);
    }

    /**
     * Listar cursos do funcionário
     */
    public function courses(): void
    {
        $user = $this->currentUser();

        $enrollmentModel = new CourseEnrollment();
        $enrollments = $enrollmentModel->getByEmployee($user['id']);

        $this->setLayout('employee');
        $this->view('employee/courses', [
            'title' => 'Meus Cursos',
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Acessar curso
     */
    public function viewCourse(int $courseId): void
    {
        $user = $this->currentUser();

        $enrollmentModel = new CourseEnrollment();
        $enrollment = $enrollmentModel->getEnrollment($courseId, $user['id']);

        if (!$enrollment) {
            $this->flash('error', 'Você não está matriculado neste curso.');
            $this->redirect('employee/courses');
        }

        // Buscar curso completo
        $courseModel = new Course();
        $course = $courseModel->getComplete($courseId);

        $this->setLayout('course');
        $this->view('employee/course-view', [
            'title' => $course['title'],
            'course' => $course,
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Visualizar aula
     */
    public function viewLesson(int $lessonId): void
    {
        $user = $this->currentUser();

        $lessonModel = new CourseLesson();
        $lesson = $lessonModel->find($lessonId);

        if (!$lesson) {
            $this->flash('error', 'Aula não encontrada.');
            $this->redirect('employee/courses');
        }

        // Verificar matrícula
        // ... (verificação adicional)

        // Gate: matrícula bloqueada ou módulo anterior não aprovado
        $moduleModel = new CourseModule();
        $courseModel = new Course();
        $enrollmentModel = new CourseEnrollment();
        $module = $moduleModel->find((int)$lesson['module_id']);
        if ($module) {
            $enrollment = $enrollmentModel->getEnrollment((int)$module['course_id'], $user['id']);
            if (!$enrollment) {
                $this->flash('error', 'Você não está matriculado neste curso.');
                $this->redirect('employee/courses');
            }
            if (!empty($enrollment['is_locked'])) {
                $this->flash('error', 'Sua matrícula está bloqueada. Solicite liberação ao gestor.');
                $this->redirect('employee/courses/' . (int)$module['course_id']);
            }
            $rows = $courseModel->query(
                "SELECT COUNT(*) as pending FROM course_modules m
                 LEFT JOIN course_module_results r ON r.module_id = m.id AND r.enrollment_id = :e
                 WHERE m.course_id = :c AND m.order_number < :ord AND (r.passed IS NULL OR r.passed = 0)",
                ['e' => $enrollment['id'], 'c' => $module['course_id'], 'ord' => $module['order_number']]
            );
            if ((int)($rows[0]['pending'] ?? 0) > 0) {
                $this->flash('error', 'Você precisa ser aprovado no módulo anterior antes de continuar.');
                $this->redirect('employee/courses/' . (int)$module['course_id']);
            }
        }

        // Buscar próxima aula
        $nextLesson = $lessonModel->getNextLesson($lessonId);

        $this->setLayout('course');
        $this->view('employee/lesson-view', [
            'title' => $lesson['title'],
            'lesson' => $lesson,
            'nextLesson' => $nextLesson,
        ]);
    }

    /**
     * Quiz de módulo do curso (exibição)
     */
    public function moduleQuiz(int $courseId, int $moduleId): void
    {
        $user = $this->currentUser();
        $enrollmentModel = new CourseEnrollment();
        $enrollment = $enrollmentModel->getEnrollment($courseId, $user['id']);
        if (!$enrollment) {
            $this->flash('error', 'Você não está matriculado neste curso.');
            $this->redirect('employee/courses');
        }
        if (!empty($enrollment['is_locked'])) {
            $this->flash('error', 'Sua matrícula está bloqueada. Solicite liberação ao gestor.');
            $this->redirect('employee/courses/' . $courseId);
        }

        $courseModel = new Course();
        $moduleModel = new CourseModule();
        $module = $moduleModel->find($moduleId);
        if (!$module) {
            $this->flash('error', 'Módulo não encontrado.');
            $this->redirect('employee/courses/' . $courseId);
        }

        // Gating: precisa passar módulos anteriores
        $prevCountRows = $courseModel->query(
            "SELECT COUNT(*) as pending FROM course_modules m
             LEFT JOIN course_module_results r ON r.module_id = m.id AND r.enrollment_id = :e
             WHERE m.course_id = :c AND m.order_number < :ord AND (r.passed IS NULL OR r.passed = 0)",
            ['e' => $enrollment['id'], 'c' => $courseId, 'ord' => $module['order_number']]
        );
        if ((int)($prevCountRows[0]['pending'] ?? 0) > 0) {
            $this->flash('error', 'Você precisa ser aprovado no módulo anterior antes de continuar.');
            $this->redirect('employee/courses/' . $courseId);
        }

        $questionModel = new CourseQuestion();
        $questions = $questionModel->getByModule($moduleId);

        $settings = new CompanySetting();
        $course = $courseModel->find($courseId);
        $maxAttempts = (int) ($settings->get((int)$course['company_id'], 'max_attempts_course', 3));
        $resultModel = new CourseModuleResult();
        $result = $resultModel->getResult((int)$enrollment['id'], (int)$moduleId);

        $this->setLayout('course');
        $this->view('employee/module-quiz', [
            'title' => 'Avaliação do Módulo',
            'course' => $course,
            'module' => $module,
            'questions' => $questions,
            'enrollment' => $enrollment,
            'result' => $result,
            'maxAttempts' => $maxAttempts,
            'csrf' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Submeter quiz do módulo (JSON)
     */
    public function submitModuleQuiz(int $courseId, int $moduleId): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
        }
        $user = $this->currentUser();
        $enrollmentModel = new CourseEnrollment();
        $enrollment = $enrollmentModel->getEnrollment($courseId, $user['id']);
        if (!$enrollment) {
            $this->json(['error' => 'Matrícula não encontrada'], 404);
        }
        if (!empty($enrollment['is_locked'])) {
            $this->json(['error' => 'Matrícula bloqueada. Solicite liberação ao gestor.'], 403);
        }

        $questionModel = new CourseQuestion();
        $questions = $questionModel->getByModule($moduleId);
        if (empty($questions)) {
            $this->json(['error' => 'Não há questões para este módulo'], 400);
        }

        // Respostas do usuário
        $inputAnswers = $this->input('answers', []);
        $processed = [];
        foreach ($questions as $q) {
            $sel = $inputAnswers[$q['id']] ?? null;
            if ($sel) {
                $processed[] = [
                    'question_id' => $q['id'],
                    'selected_option' => $sel,
                    'is_correct' => strtoupper($sel) === strtoupper($q['correct_option'])
                ];
            }
        }
        if (count($processed) < count($questions)) {
            $this->json(['error' => 'Responda todas as questões'], 400);
        }

        $answerModel = new CourseAnswer();
        $answerModel->saveBatch((int)$enrollment['id'], (int)$moduleId, $processed);
        $score = $answerModel->calculateScore((int)$enrollment['id'], (int)$moduleId);
        $passed = $score >= 70;

        $settings = new CompanySetting();
        $courseModel = new Course();
        $course = $courseModel->find($courseId);
        $maxAttempts = (int) ($settings->get((int)$course['company_id'], 'max_attempts_course', 3));

        $resultModel = new CourseModuleResult();
        $existing = $resultModel->getResult((int)$enrollment['id'], (int)$moduleId);
        $attemptsUsed = (int) ($existing['attempts'] ?? 0);
        if ($existing && !$existing['passed'] && $attemptsUsed >= $maxAttempts) {
            $this->json(['error' => 'Limite de tentativas atingido. Solicite liberação ao gestor.', 'locked' => true], 403);
        }

        $attemptsAfter = $attemptsUsed + 1;
        $lock = (!$passed && $attemptsAfter >= $maxAttempts);

        // Persistir tentativa do módulo
        $resultModel->upsertAttempt((int)$enrollment['id'], (int)$moduleId, $score, $passed, $lock);
        if ($lock) {
            // Bloquear matrícula até liberação pelo gestor
            $enrollmentModel->update((int)$enrollment['id'], ['is_locked' => 1]);
        }

        $this->json([
            'success' => true,
            'score' => $score,
            'passed' => $passed,
            'attempts' => $attemptsAfter,
            'max_attempts' => $maxAttempts,
            'locked' => $lock,
            'message' => $passed
                ? "Parabéns! Você foi aprovado com {$score}%!"
                : ($lock ? "Você obteve {$score}%. Limite de tentativas atingido. Matrícula bloqueada. Solicite liberação ao gestor." : "Você obteve {$score}%. Nota mínima: 70%. Tente novamente."),
        ]);
    }

    /**
     * Marcar aula como concluída
     */
    public function completeLesson(int $lessonId): void
    {
        $user = $this->currentUser();

        // Lógica para marcar como concluída e atualizar progresso
        // ...

        $this->json([
            'success' => true,
            'message' => 'Aula concluída!',
        ]);
    }
}
