<?php

declare(strict_types=1);

namespace PrimeSales\Services;

final class BrandingUploadService
{
    public function store(array $file, string $kind): ?string
    {
        if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ((int) $file['error'] !== UPLOAD_ERR_OK || (int) ($file['size'] ?? 0) > 2 * 1024 * 1024 || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new \RuntimeException('Le fichier est trop volumineux ou le téléversement a échoué (2 Mo maximum).');
        }

        $temporaryPath = (string) $file['tmp_name'];
        $imageInfo = @getimagesize($temporaryPath);
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        $mime = is_array($imageInfo) && $detectedMime === ($imageInfo['mime'] ?? null) ? (string) $detectedMime : '';
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
        if ($extension === null) {
            throw new \RuntimeException('Format non accepté. Utilisez une image PNG, JPG ou WebP.');
        }
        if (($imageInfo[0] ?? 0) > 5000 || ($imageInfo[1] ?? 0) > 5000) {
            throw new \RuntimeException('Les dimensions de l’image dépassent la limite autorisée.');
        }

        $directory = STORAGE_PATH . '/branding';
        if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new \RuntimeException('Le dossier storage/branding n’est pas accessible en écriture.');
        }
        $filename = $kind . '-' . bin2hex(random_bytes(12)) . '.' . $extension;
        if (!move_uploaded_file($temporaryPath, $directory . '/' . $filename)) {
            throw new \RuntimeException('Impossible d’enregistrer cette image. Vérifiez les permissions du dossier storage.');
        }
        @chmod($directory . '/' . $filename, 0640);
        return $filename;
    }
}
