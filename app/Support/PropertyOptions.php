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

    /** 物件種目 */
    public const PROPERTY_TYPES = [
        'new' => '新築',
        'used_mansion' => '中古マンション',
        'used_house' => '中古一戸建て',
        'land' => '土地',
        'other' => 'その他',
    ];

    /** 階建て: 1 / 2 / 3(=3階以上) */
    public const BUILDING_FLOORS = [
        1 => '1階建',
        2 => '2階建',
        3 => '3階以上',
    ];

    /** 駅徒歩: 5分刻み、30分超は 99 */
    public const STATION_MINUTES_OVER = 99;

    /**
     * @return list<int>
     */
    public static function stationMinutes(): array
    {
        return [5, 10, 15, 20, 25, 30, self::STATION_MINUTES_OVER];
    }

    public static function stationMinutesLabel(int $minutes): string
    {
        if ($minutes >= self::STATION_MINUTES_OVER) {
            return '30分以上';
        }

        return $minutes.'分';
    }

    /**
     * 既存値を新しい駅徒歩選択肢へ正規化。
     */
    public static function normalizeStationMinutes(int $minutes): int
    {
        if ($minutes <= 0) {
            return 5;
        }
        if ($minutes > 30) {
            return self::STATION_MINUTES_OVER;
        }

        return (int) (ceil($minutes / 5) * 5);
    }

    public static function buildingFloorsLabel(int $floors): string
    {
        return self::BUILDING_FLOORS[$floors] ?? ($floors >= 3 ? '3階以上' : $floors.'階建');
    }

    /**
     * 既存の階数を 1 / 2 / 3 に正規化。
     */
    public static function normalizeBuildingFloors(int $floors): int
    {
        if ($floors <= 1) {
            return 1;
        }
        if ($floors === 2) {
            return 2;
        }

        return 3;
    }

    /**
     * @return list<int>
     */
    public static function builtYears(?int $from = null, int $to = 1950): array
    {
        $from ??= (int) date('Y');

        return range($from, $to);
    }

    public static function builtYearLabel(int $year): string
    {
        $current = (int) date('Y');
        if ($year === $current) {
            return $year.'年（築浅・新築含む）';
        }

        $age = max(0, $current - $year);

        return $year.'年（築'.$age.'年）';
    }
}
