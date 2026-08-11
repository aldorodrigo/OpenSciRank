<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\MenuSettings;
use App\Models\CmsPost;
use App\Models\User;
use App\Support\PageVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * #62 — administración de menús: mostrar/ocultar páginas públicas y bloquear
 * el acceso por URL directa.
 */
class PageVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('super_admin', 'web');
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function superAdmin(): User
    {
        return tap(User::factory()->create())->assignRole('super_admin');
    }

    /**
     * Apaga el acceso a una página dejando el resto con sus defaults.
     */
    private function disable(string $key): void
    {
        PageVisibility::save([$key => ['enabled' => false, 'in_menu' => false]]);
    }

    public function test_public_page_is_reachable_by_default(): void
    {
        $this->get('/pricing')->assertOk();
        $this->get('/ranking')->assertOk();
    }

    public function test_disabled_page_returns_404_on_direct_url(): void
    {
        $this->disable('pricing');

        $this->get('/pricing')->assertNotFound();
        // El bloqueo aplica también a las variantes con prefijo de idioma.
        $this->get('/es/pricing')->assertNotFound();
    }

    public function test_disabled_page_link_disappears_from_header_and_footer(): void
    {
        $this->get('/')->assertSee('href="/pricing"', false);

        $this->disable('pricing');

        $this->get('/')->assertDontSee('href="/pricing"', false);
    }

    public function test_page_can_stay_reachable_while_hidden_from_menu(): void
    {
        PageVisibility::save(['ranking' => ['enabled' => true, 'in_menu' => false]]);

        $this->get('/ranking')->assertOk();
        $this->get('/')->assertDontSee('href="/ranking"', false);
    }

    public function test_ranking_link_is_present_in_the_menu_by_default(): void
    {
        $this->get('/')->assertSee('href="/ranking"', false);
    }

    public function test_disabling_the_blog_also_blocks_individual_posts(): void
    {
        $post = CmsPost::create([
            'user_id' => User::factory()->create()->id,
            'primary_locale' => 'es',
            'title' => ['es' => 'Post de prueba'],
            'content' => ['es' => 'Contenido.'],
            'type' => 'article',
            'category' => 'noticias',
            'published_at' => now()->subDay(),
        ]);

        $this->get("/blog/{$post->slug}")->assertOk();

        $this->disable('blog');

        $this->get('/blog')->assertNotFound();
        $this->get("/blog/{$post->slug}")->assertNotFound();
    }

    public function test_home_cannot_be_disabled(): void
    {
        PageVisibility::save(['home' => ['enabled' => false, 'in_menu' => false]]);

        $this->assertTrue(PageVisibility::enabled('home'));
        $this->get('/')->assertOk();
    }

    public function test_a_disabled_page_is_never_shown_in_the_menu(): void
    {
        // Aunque `in_menu` quede en true, el link no se pinta si está apagada.
        PageVisibility::save(['pricing' => ['enabled' => false, 'in_menu' => true]]);

        $this->assertFalse(PageVisibility::inMenu('pricing'));
    }

    public function test_disabled_pages_are_excluded_from_the_sitemap(): void
    {
        $path = storage_path('framework/testing/sitemap-'.uniqid().'.xml');

        $this->disable('pricing');
        $this->artisan('sitemap:generate', ['--path' => $path])->assertSuccessful();

        $xml = file_get_contents($path);
        @unlink($path);

        $this->assertStringNotContainsString('/pricing', $xml);
        $this->assertStringContainsString('/ranking', $xml);
    }

    public function test_menu_settings_page_is_restricted_to_super_admins(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/menu-settings')
            ->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->get('/admin/menu-settings')
            ->assertOk();
    }

    public function test_super_admin_can_hide_a_page_from_the_panel(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(MenuSettings::class)
            ->assertSet('data.pricing.enabled', true)
            ->set('data.pricing.enabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse(PageVisibility::enabled('pricing'));
        $this->get('/pricing')->assertNotFound();
    }
}
