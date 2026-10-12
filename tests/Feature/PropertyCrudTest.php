<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'postal_code' => '153-0064',
            'prefecture' => '東京都',
            'address' => '目黒区下目黒1-1-1',
            'latitude' => 35.6335600,
            'longitude' => 139.7157800,
            'station_minutes' => 7,
            'floor_plan' => '2LDK',
            'built_year' => 2008,
            'exclusive_area' => 58.20,
            'structure' => 'SRC造',
            'building_floors' => 12,
            'evaluation' => 'B',
            'memo' => '駅近・リフォーム済',
        ], $overrides);
    }

    private function sampleImage(string $name = 'sample.jpg'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            file_get_contents(base_path('tests/fixtures/sample.jpg'))
        );
    }

    #[Test]
    public function index_displays_properties(): void
    {
        Property::factory()->create(['name' => '表示確認用マンション']);

        $response = $this->get(route('properties.index'));

        $response->assertOk();
        $response->assertSee('候補物件');
        $response->assertSee('表示確認用マンション');
        $response->assertSee("LOAN'S");
        $response->assertSee('物件を追加');
        $response->assertSee('NO IMAGE');
        $response->assertDontSee('物件を探す');
        $response->assertDontSee('物件を掲載');
        $response->assertDontSee('中古マンション');
    }

    #[Test]
    public function index_shows_cover_image_when_present(): void
    {
        Storage::fake('public');

        $property = Property::factory()->create(['name' => '画像付き物件']);
        $path = $this->sampleImage()->store('properties/'.$property->id, 'public');
        PropertyImage::query()->create([
            'property_id' => $property->id,
            'path' => $path,
            'sort_order' => 0,
        ]);

        $response = $this->get(route('properties.index'));

        $response->assertOk();
        $response->assertSee('画像付き物件');
        $response->assertSee(Storage::disk('public')->url($path), false);
        $response->assertDontSee('>NO IMAGE<', false);
    }

    #[Test]
    public function store_creates_property(): void
    {
        $response = $this->post(route('properties.store'), $this->validPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('properties', [
            'name' => 'シティハウス目黒 501号室',
            'price' => 6200,
            'postal_code' => '153-0064',
            'prefecture' => '東京都',
            'address' => '目黒区下目黒1-1-1',
            'floor_plan' => '2LDK',
            'built_year' => 2008,
            'structure' => 'SRC造',
            'building_floors' => 12,
            'evaluation' => 'B',
        ]);
    }

    #[Test]
    public function store_accepts_multiple_images(): void
    {
        Storage::fake('public');

        $response = $this->post(route('properties.store'), $this->validPayload([
            'images' => [
                $this->sampleImage('one.jpg'),
                $this->sampleImage('two.jpg'),
            ],
        ]));

        $property = Property::query()->first();
        $this->assertNotNull($property);
        $response->assertRedirect(route('properties.show', $property));
        $this->assertDatabaseCount('property_images', 2);

        $show = $this->get(route('properties.show', $property));
        $show->assertOk();
        $show->assertSee('物件画像');
        $show->assertDontSee('NO IMAGE');
    }

    #[Test]
    public function store_rejects_invalid_data(): void
    {
        $response = $this->from(route('properties.create'))
            ->post(route('properties.store'), $this->validPayload([
                'price' => 0,
                'evaluation' => 'Z',
                'floor_plan' => 'invalid',
            ]));

        $response->assertRedirect(route('properties.create'));
        $response->assertSessionHasErrors(['price', 'evaluation', 'floor_plan']);
        $this->assertDatabaseCount('properties', 0);
    }

    #[Test]
    public function show_displays_property_details(): void
    {
        $property = Property::factory()->create([
            'name' => '詳細表示テスト',
            'price' => 9000,
            'floor_plan' => '3LDK',
            'built_year' => 2012,
            'exclusive_area' => 70.5,
            'structure' => 'RC造',
            'building_floors' => 15,
            'latitude' => 35.6656820,
            'longitude' => 139.7120880,
        ]);

        $response = $this->get(route('properties.show', $property));

        $response->assertOk();
        $response->assertSee('詳細表示テスト');
        $response->assertSee('9,000');
        $response->assertSee('3LDK');
        $response->assertSee('2012年');
        $response->assertSee('RC造 / 15階建');
        $response->assertSee('data-loans-map', false);
        $response->assertSee('35.665682', false);
    }

    #[Test]
    public function index_renders_overview_map_for_geocoded_properties(): void
    {
        Property::factory()->create([
            'name' => '地図一覧テスト',
            'latitude' => 35.6812360,
            'longitude' => 139.7671250,
        ]);

        $response = $this->get(route('properties.index'));

        $response->assertOk();
        $response->assertSee('地図');
        $response->assertSee('地図を表示');
        $response->assertDontSee('地図から物件を探す');
        $response->assertSee('data-defer-map="1"', false);
        $response->assertSee('地図一覧テスト');
    }

    #[Test]
    public function create_form_includes_interactive_map_picker(): void
    {
        $response = $this->get(route('properties.create'));

        $response->assertOk();
        $response->assertSee('物件を追加');
        $response->assertSee('郵便番号');
        $response->assertSee('都道府県');
        $response->assertSee('間取り');
        $response->assertSee('築年');
        $response->assertSee('専有面積');
        $response->assertSee('構造');
        $response->assertSee('階建て');
        $response->assertSee('画像（複数可）');
        $response->assertSee('data-postal-autofill', false);
        $response->assertSee('data-image-upload', false);
        $response->assertSee('sm:grid-cols-2', false);
        $response->assertSee('data-interactive="1"', false);
        $response->assertSee('data-lat-input="latitude"', false);
        $response->assertSee('data-loans-map', false);
        $response->assertDontSee('unpkg.com/leaflet', false);
    }

    #[Test]
    public function edit_form_omits_fixed_category_label(): void
    {
        $property = Property::factory()->create();

        $response = $this->get(route('properties.edit', $property));

        $response->assertOk();
        $response->assertSee('物件情報を編集');
        $response->assertDontSee('中古一戸建て');
        $response->assertDontSee('中古マンション');
    }

    #[Test]
    public function update_modifies_property(): void
    {
        $property = Property::factory()->create(['name' => '旧名称']);

        $response = $this->put(route('properties.update', $property), $this->validPayload([
            'name' => '更新後名称',
            'price' => 7100,
            'floor_plan' => '3LDK',
        ]));

        $response->assertRedirect(route('properties.show', $property));
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => '更新後名称',
            'price' => 7100,
            'floor_plan' => '3LDK',
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
