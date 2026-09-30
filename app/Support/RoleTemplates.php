<?php

namespace App\Support;

class RoleTemplates
{
    public const MODULES = ['dashboard', 'users', 'berita', 'prestasi', 'produk'];

    // Padanan persis dari ROLE_TEMPLATES di management.jsx
    public static function forRole(string $role): array
    {
        return match ($role) {
            'superadmin' => collect(self::MODULES)
                ->mapWithKeys(fn($m) => [$m => ['view' => true, 'edit' => true, 'del' => true]])
                ->toArray(),

            'admin' => collect(self::MODULES)
                ->mapWithKeys(fn($m) => [
                    $m => $m === 'users'
                        ? ['view' => false, 'edit' => false, 'del' => false]
                        : ['view' => true, 'edit' => true, 'del' => true]
                ])
                ->toArray(),

            'jurusan' => collect(self::MODULES)
                ->mapWithKeys(fn($m) => [
                    $m => in_array($m, ['berita', 'prestasi', 'produk'])
                        ? ['view' => true, 'edit' => true, 'del' => false]
                        : ['view' => false, 'edit' => false, 'del' => false]
                ])
                ->toArray(),

            default => collect(self::MODULES)
                ->mapWithKeys(fn($m) => [$m => ['view' => false, 'edit' => false, 'del' => false]])
                ->toArray(),
        };
    }
}
