<?php

namespace App\Services;

/**
 * FileUploadService - Serviço de upload de arquivos
 */
class FileUploadService
{
    protected string $uploadPath;
    protected array $allowedTypes;
    protected int $maxSize;

    public function __construct()
    {
        $config = require ROOT_PATH . '/config/app.php';
        $this->uploadPath = $config['upload']['path'];
        $this->allowedTypes = $config['upload']['allowed_types'];
        $this->maxSize = $config['upload']['max_size'];

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function upload(array $file, string $subfolder = ''): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception($this->getUploadError($file['error']));
        }

        if ($file['size'] > $this->maxSize) {
            throw new \Exception('Arquivo muito grande. Máximo: ' . ($this->maxSize / 1024 / 1024) . 'MB');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $this->allowedTypes)) {
            throw new \Exception('Tipo de arquivo não permitido.');
        }

        $filename = $this->generateFilename($extension);
        $targetPath = $this->uploadPath;
        
        if ($subfolder) {
            $targetPath .= '/' . trim($subfolder, '/');
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
        }

        $fullPath = $targetPath . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('Erro ao salvar arquivo.');
        }

        return [
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => $fullPath,
            'relative_path' => ($subfolder ? $subfolder . '/' : '') . $filename,
            'size' => $file['size'],
            'extension' => $extension,
        ];
    }

    public function delete(string $relativePath): bool
    {
        $fullPath = $this->uploadPath . '/' . $relativePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }

    public function readFileContent(string $path): string
    {
        if (!file_exists($path)) {
            throw new \Exception('Arquivo não encontrado.');
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'txt':
                return file_get_contents($path);
            
            case 'pdf':
                return $this->extractPdfText($path);
            
            case 'doc':
            case 'docx':
                return $this->extractWordText($path);
            
            default:
                throw new \Exception('Tipo de arquivo não suportado para leitura.');
        }
    }

    protected function extractPdfText(string $path): string
    {
        // Implementação básica - pode usar biblioteca como FPDI ou Smalot\PdfParser
        $content = shell_exec("pdftotext -enc UTF-8 \"{$path}\" -");
        return $content ?: '';
    }

    protected function extractWordText(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if ($extension === 'docx') {
            $zip = new \ZipArchive();
            
            if ($zip->open($path) === true) {
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();
                
                $xml = str_replace('</w:p>', "\n", $xml);
                return strip_tags($xml);
            }
        }
        
        return '';
    }

    protected function generateFilename(string $extension): string
    {
        return date('Y-m-d_His_') . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    protected function getUploadError(int $code): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'Upload do arquivo foi feito parcialmente.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada.',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo no disco.',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão.',
        ];

        return $errors[$code] ?? 'Erro desconhecido no upload.';
    }
}
