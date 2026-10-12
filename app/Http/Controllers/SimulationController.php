<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimulationRequest;
use App\Models\Property;
use App\Models\Simulation;
use App\Services\LoanCalculatorService;
use App\Services\SimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function __construct(
        private readonly SimulationService $simulationService
    ) {}

    public function index(): View
    {
        $simulations = $this->simulationService->list();

        return view('simulations.index', compact('simulations'));
    }

    public function create(): View
    {
        $properties = Property::query()->orderBy('name')->get();
        $selectedPropertyId = old('property_id', request()->integer('property_id') ?: null);
        $selectedProperty = $selectedPropertyId
            ? $properties->firstWhere('id', $selectedPropertyId)
            : null;

        return view('simulations.create', [
            'properties' => $properties,
            'selectedProperty' => $selectedProperty,
            'employmentTypes' => Simulation::EMPLOYMENT_LABELS,
            'defaultReviewRate' => LoanCalculatorService::DEFAULT_REVIEW_RATE_PERCENT,
        ]);
    }

    public function store(SimulationRequest $request): RedirectResponse
    {
        $simulation = $this->simulationService->create($request->validated());

        return redirect()
            ->route('simulations.show', $simulation)
            ->with('success', 'シミュレーション結果を保存しました。');
    }

    public function show(Simulation $simulation): View
    {
        $simulation->load('property');

        return view('simulations.show', compact('simulation'));
    }

    public function destroy(Simulation $simulation): RedirectResponse
    {
        $this->simulationService->delete($simulation);

        return redirect()
            ->route('simulations.index')
            ->with('success', 'シミュレーションを削除しました。');
    }
}
