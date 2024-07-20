<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateSuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $leaderRole = Role::firstOrCreate(['name' => 'Líder']);
        $executiveRole = Role::firstOrCreate(['name' => 'Ejecutivo']);
        $mechanicRole = Role::firstOrCreate(['name' => 'Mecánico']);
        $warehouseRole = Role::firstOrCreate(['name' => 'Bodeguero']);

        // Obtener todos los permisos
        $permissions = Permission::pluck('id', 'id')->all();

        // Asignar permisos a los roles
        $adminRole->syncPermissions($permissions);
        $leaderRole->syncPermissions($permissions); // Ajusta los permisos según sea necesario

        // Crear usuarios
        $users = [
            [
                'name' => 'Rodrigo Slay',
                'email' => 'webmaster@powercars.cl',
                'password' => bcrypt('Password.1'),
                'role' => $adminRole
            ],
            [
                'name' => 'Ariel Argandoña',
                'email' => 'ariel.a@powercars.cl',
                'password' => bcrypt('Password.1'),
                'role' => $leaderRole
            ],
            [
                'name' => 'Yerko Collopal',
                'email' => 'yerko.c@powercars.cl',
                'password' => bcrypt('Password.1'),
                'role' => $leaderRole
            ],
            [
                'name' => 'Kevin Portilla',
                'email' => 'kevin.p@powercars.cl',
                'password' => bcrypt('Password.1'),
                'role' => $executiveRole
            ],
            [
                'name' => 'Rodrigo Bousquet',
                'email' => 'rodrigo.b@powercars.cl',
                'password' => bcrypt('Password.1'),
                'role' => $mechanicRole
            ],
            [
                'name' => 'Juan Perez',
                'email' => 'juan.p@powercars.cl',
                'password' => bcrypt('Password.1'),
                'role' => $warehouseRole
            ],
        ];

        // Crear y asignar roles a los usuarios
        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password']
            ]);
            $user->assignRole($userData['role']);
        }
    }
}
