<?php

declare(strict_types=1);

namespace PrimeSales\Controllers;

use PrimeSales\Core\Request;

final class DashboardController extends BaseController
{
    public function home(Request $request): void
    {
        if (!$this->auth->user()) {
            redirect('/login');
        }
        redirect('/dashboard');
    }

    public function index(Request $request): void
    {
        $user = $this->auth->user();
        $isAdmin = ($user['role'] ?? '') === 'ADMIN';
        $stats = $isAdmin ? [
            ['label' => 'Commerciaux', 'value' => $this->users->countCommercials(), 'icon' => 'users', 'tone' => 'blue', 'hint' => 'équipe active'],
            ['label' => 'Catégories', 'value' => $this->categories->count(), 'icon' => 'layers', 'tone' => 'purple', 'hint' => 'types de prestations'],
            ['label' => 'Domaines', 'value' => $this->domains->count(), 'icon' => 'shapes', 'tone' => 'mint', 'hint' => 'secteurs d’activité'],
            ['label' => 'Prototypes', 'value' => $this->prototypes->count(), 'icon' => 'globe', 'tone' => 'orange', 'hint' => 'sites à présenter'],
        ] : [];

        $latest = $this->prototypes->latest(4);
        foreach ($latest as &$prototype) {
            $prototype['cover_url'] = !empty($prototype['cover_path'])
                ? url('/media/cover/' . (int) $prototype['id'])
                : asset('images/cover-fallback.svg');
        }
        unset($prototype);

        $this->page('dashboard/index', [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'stats' => $stats,
            'latest' => $latest,
            'latestLogins' => $isAdmin ? $this->loginLogs->latest(7) : [],
            'categoryCount' => $this->categories->count(),
            'domainCount' => $this->domains->count(),
        ], 'Vue d’ensemble', 'dashboard');
    }
}
