<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            ->with(['images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
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
     * @param  list<UploadedFile>  $images
     *
     * @throws ValidationException
     */
    public function create(array $data, array $images = []): Property
    {
        $validated = $this->validate($data);

        return DB::transaction(function () use ($validated, $images) {
            $property = Property::query()->create($validated);
            $this->storeImages($property, $images);

            return $property->load('images');
        });
    }

    /**
     * 物件を更新する。
     *
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $images
     * @param  list<int>  $deleteImageIds
     *
     * @throws ValidationException
     */
    public function update(Property $property, array $data, array $images = [], array $deleteImageIds = []): Property
    {
        $validated = $this->validate($data);

        return DB::transaction(function () use ($property, $validated, $images, $deleteImageIds) {
            $property->update($validated);
            $this->deleteImages($property, $deleteImageIds);
            $this->storeImages($property, $images);

            return $property->refresh()->load('images');
        });
    }

    /**
     * 物件を削除する。
     */
    public function delete(Property $property): void
    {
        DB::transaction(function () use ($property) {
            $paths = $property->images()->pluck('path')->all();
            $property->delete();

            foreach ($paths as $path) {
                Storage::disk('public')->delete($path);
            }
        });
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

    /**
     * @param  list<UploadedFile>  $images
     */
    private function storeImages(Property $property, array $images): void
    {
        if ($images === []) {
            return;
        }

        $currentCount = $property->images()->count();
        $sort = (int) ($property->images()->max('sort_order') ?? -1);

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            if ($currentCount >= 10) {
                break;
            }

            $path = $image->store('properties/'.$property->id, 'public');
            $sort++;
            $currentCount++;

            PropertyImage::query()->create([
                'property_id' => $property->id,
                'path' => $path,
                'sort_order' => $sort,
            ]);
        }
    }

    /**
     * @param  list<int>  $deleteImageIds
     */
    private function deleteImages(Property $property, array $deleteImageIds): void
    {
        if ($deleteImageIds === []) {
            return;
        }

        $images = $property->images()
            ->whereIn('id', $deleteImageIds)
            ->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }
}
