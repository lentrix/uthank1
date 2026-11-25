<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DebtorControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_can_browse_debtors_and_find_the_view_link(): void
    {
        $this->withoutVite();
        $debtorId = $this->insertDebtor();

        $response = $this->get(route('debtors.index'));

        $response->assertOk()
            ->assertSeeText('Reyes, Ana')
            ->assertSee(route('debtors.show', $debtorId));
    }

    public function test_guests_see_an_empty_state_when_there_are_no_debtors(): void
    {
        $this->withoutVite();

        $this->get(route('debtors.index'))
            ->assertOk()
            ->assertSeeText('No debtors to display.');
    }

    public function test_guests_can_open_the_blank_debtor_page(): void
    {
        $this->withoutVite();
        $debtorId = $this->insertDebtor();

        $response = $this->get(route('debtors.show', $debtorId));

        $response->assertOk()
            ->assertViewIs('debtors.show')
            ->assertDontSeeText('Ana Reyes');
    }

    public function test_the_view_page_returns_not_found_for_a_missing_debtor(): void
    {
        $this->get(route('debtors.show', 1))->assertNotFound();
    }

    private function insertDebtor(): int
    {
        return DB::table('debtors')->insertGetId([
            'fname' => 'Ana',
            'lname' => 'Reyes',
            'barangay' => 'Poblacion',
            'town' => 'Dauis',
            'province' => 'Bohol',
            'email' => 'ana@example.test',
            'cell_no' => '09000000001',
            'reference' => 'Leo Reyes',
            'reference_no' => '09000000002',
            'occupation' => 'Teacher',
            'credit_limit' => 30000,
        ]);
    }
}
