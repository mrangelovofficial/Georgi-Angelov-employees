<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadEmployeeCsvRequest;
use App\Services\EmployeePairService;
use RuntimeException;

final class TeamPeriodController extends Controller
{
    public function index()
    {
        return inertia('TeamPeriod/Index', [
            'results' => session('results', []),
            'results_count' => session('results_count', 0),
            'invalid_rows' => session('invalid_rows', 0),
        ]);
    }

    public function calculate(UploadEmployeeCsvRequest $request, EmployeePairService $employeePairService)
    {
        try {
            $results = $employeePairService->calculate(
                $request->file('file')->getRealPath()
            );

            return to_route('team-period.index')->with([
                'results' => $request->boolean('all')
                    ? iterator_to_array($results['results'])
                    : $results['results']->take(20),
                'results_count' => count($results['results']),
                'invalid_rows' => $results['invalid_rows'],
            ]);
        } catch (RuntimeException $e) {
            return to_route('team-period.index')->withErrors([
                'file' => $e->getMessage(),
            ]);
        }
    }
}
