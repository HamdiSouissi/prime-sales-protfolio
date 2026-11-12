<?php

declare(strict_types=1);

namespace PrimeSales\Services;

use RuntimeException;

/** Validates, stores and removes administrator-supplied prototype cover images. */
final class PrototypeCoverService
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_DIMENSION = 5000;

    public function store(array $file): ?string
    {
        if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Le téléversement de la couverture a échoué. Réessayez avec une image plus légère.');
        }
        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        if (!is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('Le fichier de couverture n’est pas un téléversement valide.');
        }
        if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > self::MAX_BYTES) {
            throw new RuntimeException('La couverture doit peser 5 Mo maximum.');
        }

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
            throw new RuntimeException('Format non accepté. Utilisez une image JPG, PNG ou WebP.');
        }
        if (($imageInfo[0] ?? 0) > self::MAX_DIMENSION || ($imageInfo[1] ?? 0) > self::MAX_DIMENSION) {
            throw new RuntimeException('Les dimensions de l’image dépassent la limite autorisée de 5 000 pixels.');
        }

        $directory = STORAGE_PATH . '/covers';
        if (!is_dir($directory) && !@mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Le dossier storage/covers n’est pas accessible en écriture.');
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($temporaryPath, $directory . '/' . $filename)) {
            throw new RuntimeException('Impossible d’enregistrer la couverture. Vérifiez les droits du dossier storage/covers.');
        }
        @chmod($directory . '/' . $filename, 0640);
        return $filename;
    }

    public function delete(?string $filename): void
    {
        if (!$filename || basename($filename) !== $filename) {
            return;
        }
        $path = STORAGE_PATH . '/covers/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
