<?php

namespace App\Services;

use App\Exceptions\CsvException;

class EmployeePairService
{
    public function __construct(
        private CsvEmployeeReader $csvEmployeeReader,
        private EmployeeProjectPeriodMapper $employeeProjectPeriodMapper,
        private IntervalMerger $intervalMerger,
        private OverlapCalculator $overlapCalculator,
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

        foreach ($projects as $projectId => $employees) {
            $employeeIds = array_keys($employees);
            $employeeCount = count($employeeIds);

            for ($currentIndex = 0; $currentIndex < $employeeCount - 1; $currentIndex++) {
                $currentEmployeeId = $employeeIds[$currentIndex];
                $currentEmployeePeriods = $employees[$currentEmployeeId];

                for ($comparedIndex = $currentIndex + 1; $comparedIndex < $employeeCount; $comparedIndex++) {
                    $comparedEmployeeId = $employeeIds[$comparedIndex];
                    $comparedEmployeePeriods = $employees[$comparedEmployeeId];

                    $overlapDays = $this->overlapCalculator->calculate(
                        $currentEmployeePeriods,
                        $comparedEmployeePeriods
                    );

                    if ($overlapDays > 0) {
                        $results[] = [
                            'employee1_id' => $currentEmployeeId,
                            'employee2_id' => $comparedEmployeeId,
                            'project_id' => $projectId,
                            'days_worked_together' => $overlapDays,
                        ];
                    }
                }
            }
        }

        usort($results, function (array $firstResult, array $secondResult): int {
            return $secondResult['days_worked_together']
                <=> $firstResult['days_worked_together'];
        });

        return [
            'results' => $results,
            'invalid_rows' => $invalidRows,
        ];
    }
}
