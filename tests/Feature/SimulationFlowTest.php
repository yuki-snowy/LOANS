<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Simulation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SimulationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'property_id' => null,
            'purchase_price' => 3500,
            'borrowing_amount' => 3000,
            'interest_rate' => 0.5,
            'repayment_years' => 35,
            'bonus_payment_amount' => 0,
            'management_fee' => 12000,
            'repair_reserve_fee' => 8000,
            'parking_fee' => 0,
            'annual_income' => 800,
            'employment_type' => 'full_time',
            'years_of_service' => 5,
            'existing_annual_repayment' => 0,
            'review_interest_rate' => 3.0,
        ], $overrides);
    }

    #[Test]
    public function create_form_displays(): void
    {
        $response = $this->get(route('simulations.create'));

        $response->assertOk();
        $response->assertSee('ローン試算・審査判定');
        $response->assertSee('物件価格');
        $response->assertSee('借入希望額');
        $response->assertSee('審査金利');
    }

    #[Test]
    public function store_persists_simulation_with_computed_results(): void
    {
        $property = Property::factory()->create(['price' => 3000]);

        $response = $this->post(route('simulations.store'), $this->validPayload([
            'property_id' => $property->id,
            'purchase_price' => 3000,
            'borrowing_amount' => 3000,
        ]));

        $simulation = Simulation::query()->first();
        $this->assertNotNull($simulation);
        $response->assertRedirect(route('simulations.show', $simulation));

        $this->assertDatabaseHas('simulations', [
            'property_id' => $property->id,
            'purchase_price' => 3000,
            'down_payment' => 0,
            'borrowing_amount' => 3000,
            'monthly_payment_yen' => 77876,
            'screening_status' => 'safe',
        ]);
    }

    #[Test]
    public function store_rejects_invalid_input(): void
    {
        $response = $this->from(route('simulations.create'))
            ->post(route('simulations.store'), $this->validPayload([
                'borrowing_amount' => 0,
                'annual_income' => 0,
                'employment_type' => 'unknown',
            ]));

        $response->assertRedirect(route('simulations.create'));
        $response->assertSessionHasErrors(['borrowing_amount', 'annual_income', 'employment_type']);
        $this->assertDatabaseCount('simulations', 0);
    }

    #[Test]
    public function show_displays_screening_signal_and_outflow(): void
    {
        $simulation = Simulation::factory()->create();

        $response = $this->get(route('simulations.show', $simulation));

        $response->assertOk();
        $response->assertSee('審査シグナル');
        $response->assertSee('頭金概算');
        $response->assertSee('補助金・支援制度（参考）');
        $response->assertSee('住宅ローン減税');
        $response->assertSee('実際の毎月引き落とし総額');
        $response->assertSee(number_format($simulation->actual_monthly_outflow_yen));
        $response->assertSee(number_format($simulation->down_payment));
    }

    #[Test]
    public function index_lists_simulations(): void
    {
        Simulation::factory()->create(['borrowing_amount' => 4500]);

        $response = $this->get(route('simulations.index'));

        $response->assertOk();
        $response->assertSee('4,500');
    }

    #[Test]
    public function index_paginates_simulations(): void
    {
        Simulation::factory()->count(6)->create();

        $page1 = $this->get(route('simulations.index'));
        $page1->assertOk();
        $page1->assertSee('page=2', false);

        $page2 = $this->get(route('simulations.index', ['page' => 2]));
        $page2->assertOk();
        $page2->assertSee('page=1', false);
    }

    #[Test]
    public function store_remembers_form_input_for_reuse(): void
    {
        $this->post(route('simulations.store'), $this->validPayload([
            'annual_income' => 1234,
            'interest_rate' => 0.777,
            'employment_type' => 'contract',
        ]))->assertRedirect();

        $response = $this->get(route('simulations.create'));

        $response->assertOk();
        $response->assertSee('前回の入力内容をフォームに反映しています');
        $response->assertSee('value="1234"', false);
        $response->assertSee('value="0.777"', false);
        $response->assertSee('value="contract"', false);
    }

    #[Test]
    public function create_can_reuse_specific_simulation_input(): void
    {
        $simulation = Simulation::factory()->create([
            'annual_income' => 999,
            'repayment_years' => 28,
        ]);

        $response = $this->get(route('simulations.create', ['from' => $simulation->id]));

        $response->assertOk();
        $response->assertSee('value="999"', false);
        $response->assertSee('value="28"', false);
    }

    #[Test]
    public function create_can_clear_remembered_form_input(): void
    {
        $this->post(route('simulations.store'), $this->validPayload([
            'annual_income' => 1234,
        ]))->assertRedirect();

        $this->get(route('simulations.create', ['clear_memory' => 1]))
            ->assertRedirect(route('simulations.create'));

        $response = $this->get(route('simulations.create'));
        $response->assertOk();
        $response->assertDontSee('前回の入力内容をフォームに反映しています');
        $response->assertSee('value="800"', false);
    }

    #[Test]
    public function destroy_deletes_simulation(): void
    {
        $simulation = Simulation::factory()->create();

        $response = $this->delete(route('simulations.destroy', $simulation));

        $response->assertRedirect(route('simulations.index'));
        $this->assertDatabaseMissing('simulations', ['id' => $simulation->id]);
    }

    #[Test]
    public function property_detail_links_to_simulation(): void
    {
        $property = Property::factory()->create();

        $response = $this->get(route('properties.show', $property));

        $response->assertOk();
        $response->assertSee(route('simulations.create', ['property_id' => $property->id], false), false);
    }
}
