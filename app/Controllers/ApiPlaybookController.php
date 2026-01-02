<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Playbook;
use App\Models\PlaybookQuestion;
use App\Models\TermsAcceptance;
use App\Services\OpenAIService;

/**
 * API pública para Playbooks (CRUD + vídeo)
 * Autenticada para empresas (CompanyMiddleware)
 */
class ApiPlaybookController extends Controller
{
    protected Playbook $playbookModel;
    protected PlaybookQuestion $questionModel;

    public function __construct()
    {
        $this->playbookModel = new Playbook();
        $this->questionModel = new PlaybookQuestion();
    }

    /**
     * Listar playbooks da empresa autenticada
     */
    public function index(): void
    {
        $user = $this->currentUser();
        $playbooks = $this->playbookModel->getByCompany($user['id']);
        $this->json(['success' => true, 'data' => $playbooks]);
    }

    /**
     * Detalhar playbook (inclui vídeo e questões)
     */
    public function show(int $id): void
    {
        $user = $this->currentUser();
        $playbook = $this->playbookModel->getWithQuestions($id);
        if (!$playbook || $playbook['company_id'] != $user['id']) {
            $this->json(['error' => 'Playbook não encontrado'], 404);
            return;
        }
        $this->json(['success' => true, 'data' => $playbook]);
    }

    /**
     * Criar playbook (opcional geração por IA)
     * Params:
     * - title (required)
     * - content (texto base para IA) se generate_ai=1
     * - content_html (HTML pronto) se generate_ai=0
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
            return;
        }

        $user = $this->currentUser();
        $title = trim((string)$this->input('title'));
        $generateAi = (bool)$this->input('generate_ai', false);
        $contentText = trim((string)$this->input('content'));
        $contentHtml = trim((string)$this->input('content_html'));

        if ($title === '') {
            $this->json(['error' => 'Título é obrigatório'], 400);
            return;
        }

        try {
            if ($generateAi) {
                if ($contentText === '') {
                    $this->json(['error' => 'Conteúdo base é obrigatório para geração por IA'], 400);
                    return;
                }
                $ai = new OpenAIService();
                $prompt = "Crie um playbook/treinamento corporativo completo sobre o seguinte tema:\n\n";
                $prompt .= "Título: {$title}\n\n";
                $prompt .= "Conteúdo base:\n{$contentText}\n\n";
                $prompt .= "Formate em HTML conciso, com h2/h3/p/ul/li, 800-1000 palavras.";
                $contentHtml = $ai->generateContent($prompt, $user['id']);
            }

            if ($contentHtml === '') {
                $this->json(['error' => 'Conteúdo não pode ser vazio'], 400);
                return;
            }

            $playbookId = $this->playbookModel->create([
                'company_id' => $user['id'],
                'title' => $title,
                'content_html' => $contentHtml,
                'source_type' => $generateAi ? 'text' : 'text',
                'status' => 'draft',
            ]);

            $this->json(['success' => true, 'playbook_id' => $playbookId]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Falha ao criar playbook: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Atualizar vídeo via API (url/upload/ai/none) com aceite de termos
     */
    public function updateVideo(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
            return;
        }

        $user = $this->currentUser();
        $playbook = $this->playbookModel->find($id);
        if (!$playbook || $playbook['company_id'] != $user['id']) {
            $this->json(['error' => 'Playbook não encontrado'], 404);
            return;
        }

        if (!$this->input('accept_terms')) {
            $this->json(['error' => 'É necessário aceitar os termos para prosseguir.'], 400);
            return;
        }

        $mode = $this->input('video_mode', 'none');
        $allowedModes = ['ai', 'url', 'upload', 'none'];
        if (!in_array($mode, $allowedModes, true)) {
            $mode = 'none';
        }

        $videoUrl = null;
        $videoOriginal = null;
        $previousUrl = $playbook['video_url'] ?? '';
        $removeOldUpload = function () use ($previousUrl) {
            if ($previousUrl && substr($previousUrl, 0, 27) === 'uploads/playbooks/videos/') {
                $oldPath = ROOT_PATH . '/public/' . $previousUrl;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        };

        try {
            if ($mode === 'url') {
                $videoUrl = trim((string)$this->input('video_url'));
                if ($videoUrl === '') {
                    $this->json(['error' => 'Informe um link de vídeo.'], 400);
                    return;
                }
            } elseif ($mode === 'upload') {
                if (empty($_FILES['video_file']['name']) || ($_FILES['video_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $this->json(['error' => 'Envie um arquivo de vídeo.'], 400);
                    return;
                }
                $ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];
                if (!in_array($ext, $allowedExts, true)) {
                    $this->json(['error' => 'Formato inválido. Use MP4, WEBM, OGG ou MOV.'], 400);
                    return;
                }
                $uploadDir = ROOT_PATH . '/public/uploads/playbooks/videos';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                $fileName = 'playbook_' . $id . '_' . time() . '.' . $ext;
                $targetPath = $uploadDir . '/' . $fileName;
                if (!move_uploaded_file($_FILES['video_file']['tmp_name'], $targetPath)) {
                    $this->json(['error' => 'Falha ao fazer upload do vídeo.'], 500);
                    return;
                }
                $removeOldUpload();
                $videoUrl = 'uploads/playbooks/videos/' . $fileName;
                $videoOriginal = $_FILES['video_file']['name'];
            } elseif ($mode === 'ai') {
                $aiService = new OpenAIService();
                $plain = strip_tags($playbook['content_html'] ?? '');
                $prompt = "Você é um especialista em treinamento corporativo. Indique APENAS uma URL COMPLETA de um vídeo público do YouTube em português do Brasil que seja MUITO RELEVANTE para este playbook.\n\n";
                $prompt .= "Título do playbook: {$playbook['title']}\n\n";
                $prompt .= "Resumo do conteúdo (até 600 chars):\n" . mb_substr($plain, 0, 600);
                $prompt .= "\n\nRegras:\n";
                $prompt .= "- O vídeo deve ser educacional, não entretenimento.\n";
                $prompt .= "- Precisa estar em português do Brasil.\n";
                $prompt .= "- Responda apenas com a URL do YouTube.\n";

                $response = $aiService->generateContent($prompt, $user['id'], 'playbook_video_suggestion');
                if (!preg_match('/https?:\/\/\S+/', $response, $matches)) {
                    $this->json(['error' => 'Não foi possível obter um vídeo automático. Informe um link manual.'], 400);
                    return;
                }
                $videoUrl = $matches[0];
            } elseif ($mode === 'none') {
                $removeOldUpload();
                $videoUrl = null;
                $videoOriginal = null;
            }

            $terms = new TermsAcceptance();
            $terms->recordAcceptance($user['id'], '1.0-playbook-video');

            $this->playbookModel->update($id, [
                'video_mode' => $mode,
                'video_url' => $videoUrl,
                'video_original_name' => $videoOriginal,
            ]);

            $this->json([
                'success' => true,
                'video_url' => $videoUrl,
                'video_mode' => $mode,
                'video_original_name' => $videoOriginal,
            ]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Erro ao atualizar vídeo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Deletar playbook
     */
    public function delete(int $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['error' => 'Token inválido'], 400);
            return;
        }

        $user = $this->currentUser();
        $playbook = $this->playbookModel->find($id);
        if (!$playbook || $playbook['company_id'] != $user['id']) {
            $this->json(['error' => 'Playbook não encontrado'], 404);
            return;
        }

        try {
            $this->questionModel->deleteByPlaybook($id);
            $this->playbookModel->delete($id);
            $this->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Falha ao excluir playbook: ' . $e->getMessage()], 500);
        }
    }
}
