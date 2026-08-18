<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\BookResource;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Issue #73 — estados del libro en el admin.
 *
 * La columna de la tabla sólo traducía cuatro estados: `pending_listing` (el
 * estado en que queda todo libro tras pagar el listado o recibir la cortesía) y
 * `rejected` se mostraban en crudo, y ninguno de los dos era filtrable.
 */
class BookAdminStatusTest extends TestCase
{
    use RefreshDatabase;

    /** Estados que el modelo puede tomar en el flujo de listado. */
    private const STATUSES = [
        'draft',
        'submitted',
        'pending_listing',
        'requires_changes_listing',
        'listed',
        'rejected',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Shield tiene `define_via_gate => false`: el super_admin necesita los
        // permisos asignados explícitamente, igual que en producción.
        Role::findOrCreate('super_admin', 'web')
            ->givePermissionTo(Permission::findOrCreate('ViewAny:Book', 'web'));

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_todos_los_estados_tienen_etiqueta_y_color(): void
    {
        $this->app->setLocale('es');

        $options = BookResource::statusOptions();
        $colors = BookResource::statusColors();

        foreach (self::STATUSES as $status) {
            $this->assertArrayHasKey($status, $options, "Falta la etiqueta de {$status}.");
            $this->assertNotSame($status, $options[$status], "El estado {$status} se muestra en crudo.");
            $this->assertArrayHasKey($status, $colors, "Falta el color de {$status}.");
        }

        // Un libro rechazado no puede verse igual que un borrador.
        $this->assertNotSame($colors['draft'], $colors['rejected']);
    }

    public function test_la_tabla_del_admin_muestra_pending_listing_traducido(): void
    {
        $this->app->setLocale('es');

        $admin = tap(User::factory()->create())->assignRole('super_admin');

        Book::create([
            'user_id' => $admin->id,
            'title' => ['es' => 'Cartografía de la ciencia abierta'],
            'slug' => 'cartografia-'.uniqid(),
            'primary_locale' => 'es',
            'book_type' => 'monograph',
            'status' => 'pending_listing',
        ]);

        $response = $this->actingAs($admin)->get('/admin/books')->assertOk();

        // La etiqueta se resuelve con el locale que dejó la request, no con el
        // del test: el panel no está bajo el prefijo de idioma.
        $response->assertSee(BookResource::statusOptions()['pending_listing'], escape: false);
        $response->assertDontSee('>pending_listing<', escape: false);
    }
}
