<?php

namespace Tests\Feature;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PropertyCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'シティハウス目黒 501号室',
            'price' => 6200,
            'address' => '東京都目黒区下目黒1-1-1',
            'latitude' => 35.6335600,
            'longitude' => 139.7157800,
            'station_minutes' => 7,
            'evaluation' => 'B',
            'memo' => '駅近・リフォーム済',
        ], $overrides);
    }

    #[Test]
    public function index_displays_properties(): void
    {
        Property::factory()->create(['name' => '表示確認用マンション']);

        $response = $this->get(route('properties.index'));

        $response->assertOk();
        $response->assertSee('表示確認用マンション');
        $response->assertSee("LOAN'S");
    }

    #[Test]
    public function store_creates_property(): void
    {
        $response = $this->post(route('properties.store'), $this->validPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('properties', [
            'name' => 'シティハウス目黒 501号室',
            'price' => 6200,
            'evaluation' => 'B',
        ]);
    }

    #[Test]
    public function store_rejects_invalid_data(): void
    {
        $response = $this->from(route('properties.create'))
            ->post(route('properties.store'), $this->validPayload([
                'price' => 0,
                'evaluation' => 'Z',
            ]));

        $response->assertRedirect(route('properties.create'));
        $response->assertSessionHasErrors(['price', 'evaluation']);
        $this->assertDatabaseCount('properties', 0);
    }

    #[Test]
    public function show_displays_property_details(): void
    {
        $property = Property::factory()->create([
            'name' => '詳細表示テスト',
            'price' => 9000,
            'latitude' => 35.6656820,
            'longitude' => 139.7120880,
        ]);

        $response = $this->get(route('properties.show', $property));

        $response->assertOk();
        $response->assertSee('詳細表示テスト');
        $response->assertSee('9,000');
        $response->assertSee('property-map', false);
    }

    #[Test]
    public function update_modifies_property(): void
    {
        $property = Property::factory()->create(['name' => '旧名称']);

        $response = $this->put(route('properties.update', $property), $this->validPayload([
            'name' => '更新後名称',
            'price' => 7100,
        ]));

        $response->assertRedirect(route('properties.show', $property));
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => '更新後名称',
            'price' => 7100,
        ]);
    }

    #[Test]
    public function destroy_deletes_property(): void
    {
        $property = Property::factory()->create();

        $response = $this->delete(route('properties.destroy', $property));

        $response->assertRedirect(route('properties.index'));
        $this->assertDatabaseMissing('properties', ['id' => $property->id]);
    }
}
