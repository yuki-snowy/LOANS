<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Models\Property;
use App\Services\PropertyService;
use App\Support\Prefectures;
use App\Support\PropertyOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function __construct(
        private readonly PropertyService $propertyService
    ) {}

    public function index(): View
    {
        $properties = $this->propertyService->list();

        return view('properties.index', compact('properties'));
    }

    public function create(): View
    {
        return view('properties.create', $this->formOptions());
    }

    public function store(PropertyRequest $request): RedirectResponse
    {
        [$data, $images, $deleteImageIds] = $this->extractPayload($request);
        $property = $this->propertyService->create($data, $images);

        return redirect()
            ->route('properties.show', $property)
            ->with('success', '物件を追加しました。');
    }

    public function show(Property $property): View
    {
        $property->load('images');

        return view('properties.show', compact('property'));
    }

    public function edit(Property $property): View
    {
        $property->load('images');

        return view('properties.edit', [
            'property' => $property,
            ...$this->formOptions(),
        ]);
    }

    public function update(PropertyRequest $request, Property $property): RedirectResponse
    {
        [$data, $images, $deleteImageIds] = $this->extractPayload($request);
        $this->propertyService->update($property, $data, $images, $deleteImageIds);

        return redirect()
            ->route('properties.show', $property)
            ->with('success', '物件を更新しました。');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->propertyService->delete($property);

        return redirect()
            ->route('properties.index')
            ->with('success', '物件を削除しました。');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'evaluations' => Property::EVALUATIONS,
            'prefectures' => Prefectures::ALL,
            'floorPlans' => PropertyOptions::FLOOR_PLANS,
            'structures' => PropertyOptions::STRUCTURES,
            'builtYears' => PropertyOptions::builtYears(),
            'buildingFloors' => PropertyOptions::buildingFloors(),
            'stationMinutes' => PropertyOptions::stationMinutes(),
        ];
    }

    /**
     * @return array{0: array<string, mixed>, 1: list<UploadedFile>, 2: list<int>}
     */
    private function extractPayload(PropertyRequest $request): array
    {
        $data = $request->validated();
        $deleteImageIds = array_map('intval', $data['delete_images'] ?? []);
        unset($data['images'], $data['delete_images']);

        $images = array_values(array_filter(
            $request->file('images', []) ?: [],
            fn ($file) => $file instanceof UploadedFile
        ));

        return [$data, $images, $deleteImageIds];
    }
}
