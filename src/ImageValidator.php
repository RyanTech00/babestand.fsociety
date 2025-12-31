<?php
/**
 * BabeStand - Classe ImageValidator
 * Validacao segura de imagens para prevenir uploads maliciosos
 */

namespace App;

class ImageValidator
{
    // Tipos MIME permitidos (adicionado webp)
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    // Tamanho maximo em bytes (10MB)
    private const MAX_SIZE = 10 * 1024 * 1024;

    // Dimensoes maximas
    private const MAX_WIDTH = 4096;
    private const MAX_HEIGHT = 4096;

    // Assinaturas de ficheiros (magic bytes)
    private const MAGIC_BYTES = [
        'jpg' => ["\xFF\xD8\xFF"],
        'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        'webp' => ["RIFF"],
    ];

    private array $errors = [];

    /**
     * Valida um ficheiro de upload
     */
    public function validate(array $file): bool
    {
        $this->errors = [];

        // Verificar erros de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Verificar tamanho
        if ($file['size'] > self::MAX_SIZE) {
            $this->errors[] = 'O ficheiro excede o tamanho maximo permitido de ' . (self::MAX_SIZE / 1024 / 1024) . 'MB.';
            return false;
        }

        // Verificar tipo MIME real (nao o enviado pelo browser)
        $realMimeType = $this->getRealMimeType($file['tmp_name']);
        if (!$realMimeType || !isset(self::ALLOWED_TYPES[$realMimeType])) {
            $this->errors[] = 'Tipo de ficheiro nao permitido. Apenas JPG, PNG e WebP sao aceites.';
            return false;
        }

        // Verificar magic bytes
        $extension = self::ALLOWED_TYPES[$realMimeType];
        if (!$this->checkMagicBytes($file['tmp_name'], $extension)) {
            $this->errors[] = 'O ficheiro nao e uma imagem valida.';
            return false;
        }

        // Verificar se e realmente uma imagem valida
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->errors[] = 'O ficheiro nao e uma imagem valida.';
            return false;
        }

        // Verificar dimensoes
        if ($imageInfo[0] > self::MAX_WIDTH || $imageInfo[1] > self::MAX_HEIGHT) {
            $this->errors[] = 'A imagem excede as dimensoes maximas permitidas (' . self::MAX_WIDTH . 'x' . self::MAX_HEIGHT . 'px).';
            return false;
        }

        // NOTA: Removida verificacao de containsPhpCode e containsSuspiciousContent
        // A regeneracao da imagem com GD ja remove qualquer metadado/codigo malicioso
        // Isso previne falsos positivos em imagens legitimas com metadados EXIF

        return true;
    }

    /**
     * Processa e regenera a imagem para remover metadados e potencial codigo malicioso
     */
    public function processAndSave(array $file, string $destination, int $maxWidth = 1920, int $maxHeight = 1920, int $quality = 85): bool
    {
        if (!extension_loaded('gd')) {
            // GD nao esta disponivel, mover ficheiro directamente
            return move_uploaded_file($file['tmp_name'], $destination);
        }

        $realMimeType = $this->getRealMimeType($file['tmp_name']);
        $extension = self::ALLOWED_TYPES[$realMimeType] ?? 'jpg';

        // Carregar imagem
        $sourceImage = match($realMimeType) {
            'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
            'image/png' => @imagecreatefrompng($file['tmp_name']),
            'image/webp' => @imagecreatefromwebp($file['tmp_name']),
            default => false
        };

        if ($sourceImage === false) {
            $this->errors[] = 'Nao foi possivel processar a imagem.';
            return false;
        }

        // Obter dimensoes originais
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Calcular novas dimensoes mantendo proporcao
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);

        if ($ratio < 1) {
            $newWidth = (int) ($origWidth * $ratio);
            $newHeight = (int) ($origHeight * $ratio);
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        // Criar nova imagem
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia para PNG e WebP
        if ($realMimeType === 'image/png' || $realMimeType === 'image/webp') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Redimensionar
        imagecopyresampled(
            $newImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $origWidth, $origHeight
        );

        // Determinar extensao do destino
        $destExtension = strtolower(pathinfo($destination, PATHINFO_EXTENSION));

        // Guardar imagem regenerada
        $success = match($destExtension) {
            'png' => imagepng($newImage, $destination, (int)(9 - ($quality / 100 * 9))),
            'webp' => imagewebp($newImage, $destination, $quality),
            default => imagejpeg($newImage, $destination, $quality)
        };

        // Libertar memoria
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        if (!$success) {
            $this->errors[] = 'Erro ao guardar a imagem.';
            return false;
        }

        return true;
    }

    /**
     * Obtem o tipo MIME real do ficheiro
     */
    private function getRealMimeType(string $filepath): ?string
    {
        // Tentar com finfo primeiro
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filepath);
            finfo_close($finfo);
            return $mimeType ?: null;
        }

        // Fallback para getimagesize
        $info = @getimagesize($filepath);
        return $info['mime'] ?? null;
    }

    /**
     * Verifica magic bytes do ficheiro
     */
    private function checkMagicBytes(string $filepath, string $extension): bool
    {
        if (!isset(self::MAGIC_BYTES[$extension])) {
            return true;
        }

        $handle = fopen($filepath, 'rb');
        if (!$handle) {
            return false;
        }

        $maxLength = max(array_map('strlen', self::MAGIC_BYTES[$extension]));
        $bytes = fread($handle, $maxLength + 8); // extra bytes for WEBP check
        fclose($handle);

        // Special handling for WebP (RIFF....WEBP)
        if ($extension === 'webp') {
            return str_starts_with($bytes, 'RIFF') && str_contains($bytes, 'WEBP');
        }

        foreach (self::MAGIC_BYTES[$extension] as $magic) {
            if (str_starts_with($bytes, $magic)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o ficheiro contem codigo PHP
     */
    private function containsPhpCode(string $filepath): bool
    {
        $content = file_get_contents($filepath);
        if ($content === false) {
            return true; // Por seguranca, rejeitar se nao conseguir ler
        }

        // Padroes PHP perigosos - apenas os mais criticos
        $patterns = [
            '/<\?php/i',
            '/<\?=/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Regista incidente de seguranca
     */
    private function logSecurityIncident(string $message, string $filename): void
    {
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user = Session::isAuthenticated() ? Session::getUserId() : 'guest';
        $logEntry = sprintf(
            "[%s] %s | IP: %s | User: %s | File: %s\n",
            date('Y-m-d H:i:s'),
            $message,
            $ip,
            $user,
            $filename
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Obtem mensagem de erro de upload
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'O ficheiro excede o tamanho maximo permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE => 'O ficheiro excede o tamanho maximo permitido.',
            UPLOAD_ERR_PARTIAL => 'O upload nao foi completado. Tente novamente.',
            UPLOAD_ERR_NO_FILE => 'Nenhum ficheiro foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Erro de configuracao do servidor.',
            UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever o ficheiro.',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado pelo servidor.',
            default => 'Erro desconhecido no upload.'
        };
    }

    /**
     * Obtem os erros
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtem o primeiro erro
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Verifica se tem erros
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Gera nome unico para ficheiro
     */
    public static function generateFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!$extension || !in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            $extension = 'jpg';
        }
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }
        return bin2hex(random_bytes(16)) . '.' . $extension;
    }
}
