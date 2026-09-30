<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\RoleTemplates;
use Illuminate\Console\Command;

/**
 * Setiap kali MODULES di RoleTemplates.php (dan management.jsx) nambah
 * modul baru, akun yang sudah dibuat SEBELUM modul itu ada tidak otomatis
 * punya baris permission untuk modul baru itu — jadinya ditolak akses
 * (403 "Kamu tidak punya akses ke aksi ini.") walau seharusnya boleh
 * sesuai default role-nya.
 *
 * Command ini mengisi baris yang HILANG saja, sesuai default
 * RoleTemplates::forRole($user->role) — baris yang sudah ada (termasuk
 * yang sudah dikustom manual lewat form Edit User) TIDAK disentuh/ditimpa.
 *
 * Jalankan sekali lewat: php artisan permissions:sync
 * Jalankan lagi setiap kali menambah modul baru ke MODULES.
 */
class SyncUserPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Backfill baris user_permissions yang belum ada untuk modul-modul baru, sesuai default RoleTemplates per role';

    public function handle(): int
    {
        $users = User::with('permissions')->get();
        $created = 0;

        foreach ($users as $user) {
            $existingModules = $user->permissions->pluck('module')->all();
            $defaults = RoleTemplates::forRole($user->role);

            foreach ($defaults as $module => $perm) {
                if (in_array($module, $existingModules, true)) {
                    continue; // sudah ada -> jangan ditimpa, mungkin sudah dikustom
                }

                $user->permissions()->create([
                    'module' => $module,
                    'can_view' => $perm['view'],
                    'can_edit' => $perm['edit'],
                    'can_delete' => $perm['del'],
                ]);

                $this->line("+ {$user->email}: {$module}");
                $created++;
            }
        }

        $this->info("Selesai. {$created} baris permission baru ditambahkan.");

        return self::SUCCESS;
    }
}
