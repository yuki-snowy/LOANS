<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PropertyValidationTest extends TestCase
{
    private PropertyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PropertyService;
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'パークタワー青山 1203号室',
            'property_type' => 'used_mansion',
            'price' => 8500,
            'postal_code' => '107-0062',
            'prefecture' => '東京都',
            'address' => '港区南青山1-2-3',
            'latitude' => 35.6656820,
            'longitude' => 139.7120880,
            'station_minutes' => 5,
            'floor_plan' => '3LDK',
            'built_year' => 2015,
            'exclusive_area' => 78.45,
            'structure' => 'RC造',
            'building_floors' => 3,
            'evaluation' => 'A',
            'memo' => '南向き・眺望良好',
        ], $overrides);
    }

    #[Test]
    public function valid_payload_passes_validation(): void
    {
        $validated = $this->service->validate($this->validPayload());

        $this->assertSame('パークタワー青山 1203号室', $validated['name']);
        $this->assertSame(8500, $validated['price']);
        $this->assertSame('3LDK', $validated['floor_plan']);
        $this->assertSame('A', $validated['evaluation']);
    }

    #[Test]
    public function nullable_fields_can_be_null(): void
    {
        $validated = $this->service->validate($this->validPayload([
            'latitude' => null,
            'longitude' => null,
            'evaluation' => null,
            'memo' => null,
            'postal_code' => null,
        ]));

        $this->assertNull($validated['latitude']);
        $this->assertNull($validated['longitude']);
        $this->assertNull($validated['evaluation']);
        $this->assertNull($validated['memo']);
    }

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function invalid_payload_fails_validation(array $overrides, string $expectedField): void
    {
        try {
            $this->service->validate($this->validPayload($overrides));
            $this->fail('ValidationException が発生しませんでした。');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($expectedField, $e->errors());
        }
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function invalidPayloadProvider(): array
    {
        return [
            'name missing' => [['name' => ''], 'name'],
            'price zero' => [['price' => 0], 'price'],
            'price negative' => [['price' => -1], 'price'],
            'address missing' => [['address' => ''], 'address'],
            'prefecture missing' => [['prefecture' => ''], 'prefecture'],
            'prefecture invalid' => [['prefecture' => '東京'], 'prefecture'],
            'postal code invalid' => [['postal_code' => '123'], 'postal_code'],
            'floor plan invalid' => [['floor_plan' => '10LDK'], 'floor_plan'],
            'structure invalid' => [['structure' => '石造'], 'structure'],
            'built year too old' => [['built_year' => 1800], 'built_year'],
            'exclusive area zero' => [['exclusive_area' => 0], 'exclusive_area'],
            'building floors invalid' => [['building_floors' => 12], 'building_floors'],
            'property type invalid' => [['property_type' => 'villa'], 'property_type'],
            'latitude out of range' => [['latitude' => 91], 'latitude'],
            'longitude out of range' => [['longitude' => -181], 'longitude'],
            'station minutes invalid' => [['station_minutes' => 7], 'station_minutes'],
            'invalid evaluation' => [['evaluation' => 'Z'], 'evaluation'],
        ];
    }

    #[Test]
    public function evaluation_rules_only_allow_s_a_b_c(): void
    {
        $this->assertSame(['S', 'A', 'B', 'C'], Property::EVALUATIONS);

        foreach (Property::EVALUATIONS as $grade) {
            $validated = $this->service->validate($this->validPayload([
                'evaluation' => $grade,
            ]));

            $this->assertSame($grade, $validated['evaluation']);
        }
    }

    #[Test]
    public function has_coordinates_requires_both_latitude_and_longitude(): void
    {
        $property = new Property([
            'latitude' => 35.0,
            'longitude' => 139.0,
        ]);
        $this->assertTrue($property->hasCoordinates());

        $property->longitude = null;
        $this->assertFalse($property->hasCoordinates());
    }
}
