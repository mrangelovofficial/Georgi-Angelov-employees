<?php

namespace App\Services;

use App\Exceptions\CsvException;

class EmployeePairService
{
    public function __construct(
        private CsvEmployeeReader $csvEmployeeReader,
        private EmployeeProjectPeriodMapper $employeeProjectPeriodMapper,
        private IntervalMerger $intervalMerger,
    ) {}

    public function calculate(string $path): array
    {
        $projects = [];
        $invalidRows = 0;

        foreach ($this->csvEmployeeReader->read($path) as $row) {
            try {
                $period = $this->employeeProjectPeriodMapper->map($row);

                $projects[$period->projectId][$period->employeeId][] = $period;
            } catch (CsvException) {
                $invalidRows++;
            }
        }

        foreach ($projects as $projectId => $employees) {
            foreach ($employees as $employeeId => $periods) {
                $projects[$projectId][$employeeId] =
                    $this->intervalMerger->merge($periods);
            }
        }

        return [
            'projects' => $projects,
            'invalid_rows' => $invalidRows,
        ];
    }
}
