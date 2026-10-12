<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\RedirectResponse;
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
        return view('properties.create', [
            'evaluations' => Property::EVALUATIONS,
        ]);
    }

    public function store(PropertyRequest $request): RedirectResponse
    {
        $property = $this->propertyService->create($request->validated());

        return redirect()
            ->route('properties.show', $property)
            ->with('success', '物件を登録しました。');
    }

    public function show(Property $property): View
    {
        return view('properties.show', compact('property'));
    }

    public function edit(Property $property): View
    {
        return view('properties.edit', [
            'property' => $property,
            'evaluations' => Property::EVALUATIONS,
        ]);
    }

    public function update(PropertyRequest $request, Property $property): RedirectResponse
    {
        $this->propertyService->update($property, $request->validated());

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
}
