<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PropertyService
{
    /**
     * 物件一覧を取得する（新しい順）。
     *
     * @return LengthAwarePaginator<int, Property>
     */
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Property::query()
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * 全物件を取得する（地図表示など）。
     *
     * @return Collection<int, Property>
     */
    public function all(): Collection
    {
        return Property::query()->orderByDesc('id')->get();
    }

    /**
     * 物件を作成する。
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function create(array $data): Property
    {
        $validated = $this->validate($data);

        return DB::transaction(fn () => Property::query()->create($validated));
    }

    /**
     * 物件を更新する。
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function update(Property $property, array $data): Property
    {
        $validated = $this->validate($data);

        return DB::transaction(function () use ($property, $validated) {
            $property->update($validated);

            return $property->refresh();
        });
    }

    /**
     * 物件を削除する。
     */
    public function delete(Property $property): void
    {
        DB::transaction(fn () => $property->delete());
    }

    /**
     * 入力データを検証する。
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $validator = Validator::make(
            $data,
            Property::rules(),
            [],
            Property::attributeLabels()
        );

        return $validator->validate();
    }
}
