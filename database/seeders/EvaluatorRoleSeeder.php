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

        // Roadmap #35 (escritorio del evaluator) — set FINAL de permisos. La
        // acotación del rol NO se logra revocando permisos, sino por
        // navegación/gating:
        //   - ViewAny/View/Update:Journal se MANTIENEN aunque el evaluator no
        //     navegue el recurso Journals: EvaluateJournal::authorizeResourceAccess()
        //     depende de canViewAny() (= ViewAny:Journal). Se ocultan del nav vía
        //     JournalResource::shouldRegisterNavigation()=false y se bloquea la
        //     URL /admin/journals en ListJournals::mount().
        //   - Los AdminTask se scopean a assigned_to (getEloquentQuery), sin
        //     auto-pickup de tasks sin asignar.
        // NO agregar permisos de Payment/User/Product/Category/CriteriaItem/
        // Cms*/Book/SlaSettings ni de widgets: esas superficies quedan fuera
        // por diseño (ver issue #35).
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
