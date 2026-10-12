<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimulationRequest;
use App\Models\Property;
use App\Models\Simulation;
use App\Services\LoanCalculatorService;
use App\Services\SimulationService;
use App\Support\SimulationFormMemory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function __construct(
        private readonly SimulationService $simulationService
    ) {}

    public function index(): View
    {
        $simulations = $this->simulationService->list(5);

        return view('simulations.index', compact('simulations'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->boolean('clear_memory')) {
            SimulationFormMemory::forget();

            return redirect()
                ->route('simulations.create', array_filter([
                    'property_id' => $request->input('property_id'),
                ]))
                ->with('success', '保存していた入力内容をクリアしました。');
        }

        $properties = Property::query()->orderBy('name')->get();

        $defaults = $this->resolveFormDefaults($request, $properties);
        $selectedPropertyId = $defaults['property_id'] ?? null;
        $selectedProperty = $selectedPropertyId
            ? $properties->firstWhere('id', (int) $selectedPropertyId)
            : null;

        return view('simulations.create', [
            'properties' => $properties,
            'selectedProperty' => $selectedProperty,
            'defaults' => $defaults,
            'hasFormMemory' => SimulationFormMemory::has(),
            'employmentTypes' => Simulation::EMPLOYMENT_LABELS,
            'defaultReviewRate' => LoanCalculatorService::DEFAULT_REVIEW_RATE_PERCENT,
        ]);
    }

    public function store(SimulationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $simulation = $this->simulationService->create($validated);
        SimulationFormMemory::remember($validated);

        return redirect()
            ->route('simulations.show', $simulation)
            ->with('success', 'シミュレーション結果を保存しました。入力内容は次回の試算フォームに引き継がれます。');
    }

    public function show(Simulation $simulation): View
    {
        $simulation->load('property');

        return view('simulations.show', [
            'simulation' => $simulation,
            'subsidies' => config('subsidies', []),
        ]);
    }

    public function destroy(Simulation $simulation): RedirectResponse
    {
        $this->simulationService->delete($simulation);

        return redirect()
            ->route('simulations.index')
            ->with('success', 'シミュレーションを削除しました。');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Property>  $properties
     * @return array<string, mixed>
     */
    private function resolveFormDefaults(Request $request, $properties): array
    {
        $defaults = [
            'property_id' => null,
            'purchase_price' => 3000,
            'borrowing_amount' => 3000,
            'interest_rate' => '0.500',
            'repayment_years' => 35,
            'bonus_payment_amount' => 0,
            'management_fee' => 12000,
            'repair_reserve_fee' => 8000,
            'parking_fee' => 0,
            'annual_income' => 800,
            'employment_type' => 'full_time',
            'years_of_service' => 5,
            'existing_annual_repayment' => 0,
            'review_interest_rate' => number_format(
                LoanCalculatorService::DEFAULT_REVIEW_RATE_PERCENT,
                3,
                '.',
                ''
            ),
        ];

        $defaults = array_merge($defaults, SimulationFormMemory::recall());

        if ($fromId = $request->integer('from')) {
            $source = Simulation::query()->find($fromId);
            if ($source) {
                $fromDefaults = SimulationFormMemory::fromSimulation($source);
                if (isset($fromDefaults['interest_rate'])) {
                    $fromDefaults['interest_rate'] = number_format((float) $fromDefaults['interest_rate'], 3, '.', '');
                }
                if (isset($fromDefaults['review_interest_rate'])) {
                    $fromDefaults['review_interest_rate'] = number_format((float) $fromDefaults['review_interest_rate'], 3, '.', '');
                }
                $defaults = array_merge($defaults, $fromDefaults);
            }
        }

        $queryPropertyId = $request->integer('property_id') ?: null;
        if ($queryPropertyId) {
            $property = $properties->firstWhere('id', $queryPropertyId);
            if ($property) {
                $defaults['property_id'] = $property->id;
                // 物件リンクから来た場合は物件価格を優先
                if (! $request->integer('from')) {
                    $defaults['purchase_price'] = $property->price;
                    $defaults['borrowing_amount'] = $property->price;
                }
            }
        }

        return $defaults;
    }
}
