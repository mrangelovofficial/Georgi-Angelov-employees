<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadEmployeeCsvRequest;
use App\Services\EmployeePairService;
use RuntimeException;

final class TeamPeriodController extends Controller
{
    public function index()
    {
        return inertia('TeamPeriod/Index');
    }

    public function calculate(UploadEmployeeCsvRequest $request, EmployeePairService $employeePairService)
    {

        try {
            $results = $employeePairService->calculate(
                $request->file('file')
            );

            return inertia('TeamPeriod/Index', [
                'results' => $results['results'],
                'invalid_rows' => $results['invalid_rows'],
            ]);
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'file' => $e->getMessage(),
            ]);
        }
    }
}
