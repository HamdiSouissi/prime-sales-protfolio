<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PrimeSales\Core\Request;
use PrimeSales\Services\BrandingUploadService;
use Throwable;

final class SettingsController extends BaseController
{
    public function index(Request $request): void
    {
        $this->page('settings/index', ['settingsData' => $this->settings->all()], 'Paramètres de l’espace', 'settings');
    }

    public function update(Request $request): void
    {
        $companyName = $this->textInput($request->input('company_name'), 120);
        $primary = strtoupper(trim((string) $request->input('primary_color', '')));
        $accent = strtoupper(trim((string) $request->input('accent_color', '')));
        if (mb_strlen($companyName, 'UTF-8') < 2 || !preg_match('/^#[0-9A-F]{6}$/', $primary) || !preg_match('/^#[0-9A-F]{6}$/', $accent)) {
            flash('danger', 'Vérifiez le nom de l’entreprise et les deux couleurs hexadécimales.');
            redirect('/settings');
        }

        $uploadService = new BrandingUploadService();
        $newLogo = null;
        $newFavicon = null;
        try {
            $newLogo = $uploadService->store($_FILES['logo'] ?? [], 'logo');
            $newFavicon = $uploadService->store($_FILES['favicon'] ?? [], 'favicon');
            $oldLogo = $this->settings->get('logo_filename');
            $oldFavicon = $this->settings->get('favicon_filename');

            $logoFilename = $newLogo ?? $oldLogo;
            $faviconFilename = $newFavicon ?? $oldFavicon;
            if ($request->input('remove_logo') === '1') {
                if ($newLogo) {
                    @unlink(STORAGE_PATH . '/branding/' . $newLogo);
                    $newLogo = null;
                }
                $logoFilename = '';
            }
            if ($request->input('remove_favicon') === '1') {
                if ($newFavicon) {
                    @unlink(STORAGE_PATH . '/branding/' . $newFavicon);
                    $newFavicon = null;
                }
                $faviconFilename = '';
            }

            $this->settings->set('company_name', $companyName);
            $this->settings->set('primary_color', $primary);
            $this->settings->set('accent_color', $accent);
            $this->settings->set('logo_filename', $logoFilename);
            $this->settings->set('favicon_filename', $faviconFilename);

            foreach ([[$oldLogo, $logoFilename], [$oldFavicon, $faviconFilename]] as [$oldFile, $currentFile]) {
                if ($oldFile && $oldFile !== $currentFile && basename($oldFile) === $oldFile) {
                    $path = STORAGE_PATH . '/branding/' . $oldFile;
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
            flash('success', 'Les paramètres de marque ont été enregistrés.');
        } catch (Throwable $exception) {
            if ($newLogo) {
                @unlink(STORAGE_PATH . '/branding/' . $newLogo);
            }
            if ($newFavicon) {
                @unlink(STORAGE_PATH . '/branding/' . $newFavicon);
            }
            flash('danger', $exception instanceof \RuntimeException ? $exception->getMessage() : 'Impossible d’enregistrer les paramètres.');
        }
        redirect('/settings');
    }
}
