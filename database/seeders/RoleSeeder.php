<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = ['admin', 'manajer_proyek', 'petugas_lapangan', 'klien'];
        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        $akun = [
            ['Admin',   'admin@contoh.test',   'admin'],
            ['Manajer', 'manajer@contoh.test', 'manajer_proyek'],
            ['Petugas', 'petugas@contoh.test', 'petugas_lapangan'],
            ['Klien',   'klien@contoh.test',   'klien'],
        ];

        foreach ($akun as [$nama, $email, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $nama, 'password' => bcrypt('password')]
            );
            $user->syncRoles([$role]);
        }
    }
}