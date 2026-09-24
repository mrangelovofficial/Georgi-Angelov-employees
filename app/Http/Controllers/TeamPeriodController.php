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
            //Test results
            dd($results);
            return inertia('TeamPeriod/Index', [
                'results' => $results,
            ]);
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'file' => $e->getMessage(),
            ]);
        }
    }
}
