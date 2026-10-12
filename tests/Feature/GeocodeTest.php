<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeocodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    #[Test]
    public function postal_search_returns_coordinates_from_zipcloud_and_gsi(): void
    {
        Http::fake([
            'zipcloud.ibsnet.co.jp/*' => Http::response([
                'status' => 200,
                'results' => [[
                    'address1' => '東京都',
                    'address2' => '港区',
                    'address3' => '南青山',
                    'zipcode' => '1070062',
                ]],
            ]),
            'msearch.gsi.go.jp/*' => Http::response([[
                'geometry' => [
                    'coordinates' => [139.751663, 35.658054],
                    'type' => 'Point',
                ],
                'properties' => [
                    'title' => '東京都港区南青山',
                ],
            ]]),
        ]);

        $response = $this->getJson(route('geocode.search', ['q' => '107-0062']));

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('postal_code', '107-0062')
            ->assertJsonPath('prefecture', '東京都')
            ->assertJsonPath('latitude', 35.658054)
            ->assertJsonPath('longitude', 139.751663);
    }

    #[Test]
    public function address_search_uses_gsi(): void
    {
        Http::fake([
            'msearch.gsi.go.jp/*' => Http::response([[
                'geometry' => [
                    'coordinates' => [139.767125, 35.681236],
                    'type' => 'Point',
                ],
                'properties' => [
                    'title' => '東京都千代田区丸の内',
                ],
            ]]),
        ]);

        $response = $this->getJson(route('geocode.search', ['q' => '東京都千代田区丸の内']));

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('prefecture', '東京都')
            ->assertJsonPath('address', '千代田区丸の内');
    }

    #[Test]
    public function reverse_uses_heartrails(): void
    {
        Http::fake([
            'geoapi.heartrails.com/*' => Http::response([
                'response' => [
                    'location' => [[
                        'prefecture' => '東京都',
                        'city' => '港区',
                        'town' => '南青山',
                        'postal' => '1070062',
                        'x' => '139.712',
                        'y' => '35.665',
                    ]],
                ],
            ]),
        ]);

        $response = $this->getJson(route('geocode.reverse', [
            'latitude' => 35.665,
            'longitude' => 139.712,
        ]));

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('postal_code', '107-0062')
            ->assertJsonPath('prefecture', '東京都');
    }

    #[Test]
    public function create_form_includes_location_search(): void
    {
        $response = $this->get(route('properties.create'));

        $response->assertOk();
        $response->assertSee('郵便番号・住所から地図検索');
        $response->assertSee('data-location-search', false);
        $response->assertSee('国土地理院');
        $response->assertDontSee('unpkg.com/leaflet', false);
        $response->assertDontSee('@fonts', false);
    }
}
