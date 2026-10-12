<?php

namespace App\Support;

final class PropertyOptions
{
    /** @var list<string> */
    public const FLOOR_PLANS = [
        'ワンルーム',
        '1K',
        '1DK',
        '1LDK',
        '2K',
        '2DK',
        '2LDK',
        '3K',
        '3DK',
        '3LDK',
        '4K',
        '4DK',
        '4LDK',
        '5LDK以上',
    ];

    /** @var list<string> */
    public const STRUCTURES = [
        'RC造',
        'SRC造',
        '鉄骨造',
        '木造',
        '軽量鉄骨造',
        'その他',
    ];

    /**
     * @return list<int>
     */
    public static function builtYears(?int $from = null, int $to = 1950): array
    {
        $from ??= (int) date('Y');

        return range($from, $to);
    }

    /**
     * @return list<int>
     */
    public static function buildingFloors(int $max = 60): array
    {
        return range(1, $max);
    }

    /**
     * @return list<int>
     */
    public static function stationMinutes(): array
    {
        $minutes = range(0, 30);
        foreach ([35, 40, 45, 50, 60, 90, 120] as $extra) {
            $minutes[] = $extra;
        }

        return $minutes;
    }
}
