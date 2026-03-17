<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BookSubmissionTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function testBookSubmissionForm(): void
    {
        // Seed some basic data if needed, or just create a user
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visitRoute('app.book.submit')
                    ->waitForText('Identificación y Editorial', 10)
                    ->type('@title-input', 'Test Automated Book via Dusk') // Assuming we can just type 'title' 
                    // ... Actually wait, Dusk interacts with standard HTML names
                    ->type('title', 'Advanced AI Systems test book')
                    ->type('publisher', 'Test Pub')
                    ->type('publisher_country', 'US')
                    ->select('book_type', 'monograph')
                    ->select('primary_language', 'en')
                    ->press('Siguiente') // This might need the actual wire:click name or button text
                    ->pause(1000)
                    ->press('Guardar Borrador') // Let's try the save and exit action
                    ->pause(2000)
                    ->assertPathIs('/app'); // Route app.dashboard
        });
    }
}
