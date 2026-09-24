<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadEmployeeCsvRequest;

final class TeamPeriodController extends Controller
{
    public function index()
    {

        return inertia('TeamPeriod/Index');
    }

    public function calculate(UploadEmployeeCsvRequest $request)
    {
        $file = $request->file('file');

        // Process the uploaded file and perform calculations
        // ...

        // return redirect()->route('team-period.index')->with('success', 'Calculations completed successfully.');
    }
}
