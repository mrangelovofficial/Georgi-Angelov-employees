<?php

namespace App\Services;

use App\Exceptions\CsvException;

class EmployeePairService
{
    public function __construct(
        private CsvEmployeeReader $csvEmployeeReader,
        private EmployeeProjectPeriodMapper $employeeProjectPeriodMapper,
        private ProjectPeriodStore $projectPeriodStore,
        private ProjectPairCalculator $projectPairCalculator,
    ) {}

    public function calculate(string $path): array
    {
        $results = EmployeePairResults::create();
        $invalidRows = 0;

        try {
            foreach ($this->csvEmployeeReader->read($path) as $row) {
                try {
                    $period = $this->employeeProjectPeriodMapper->map($row);
                } catch (CsvException) {
                    $invalidRows++;

                    continue;
                }

                $this->projectPeriodStore->add($period);
            }

            foreach ($this->projectPeriodStore->projects() as $employees) {
                foreach ($this->projectPairCalculator->calculate($employees) as $result) {
                    $results->add($result);
                }
            }
        } finally {
            $this->projectPeriodStore->clear();
        }

        $results->flush();

        return [
            'results' => $results,
            'invalid_rows' => $invalidRows,
        ];
    }
}
