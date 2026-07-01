<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Roadmap #35 — rol evaluator: acceso acotado al flujo de evaluación
 * pagada de journals (assigned_evaluator_id) y a sus propias AdminTasks
 * (assigned_to). Sin acceso a Book, listing review, ni gestión de otros
 * usuarios/tareas. Debe correr después de `shield:generate` para que los
 * permisos ya existan en la tabla `permissions`.
 */
class EvaluatorRoleSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'evaluator', 'guard_name' => 'web']);

        $permissionNames = [
            'ViewAny:Journal',
            'View:Journal',
            'Update:Journal',
            'ViewAny:AdminTask',
            'View:AdminTask',
            'Update:AdminTask',
        ];

        $permissions = Permission::whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->get();

        $role->syncPermissions($permissions);

        $this->command->info('Rol evaluator: '.$permissions->count().'/'.count($permissionNames).' permisos asignados.');
    }
}
