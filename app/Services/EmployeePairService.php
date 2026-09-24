<?php

namespace App\Services;

use App\Exceptions\CsvException;

class EmployeePairService
{
    public function __construct(
        private CsvEmployeeReader $csvEmployeeReader,
        private EmployeeProjectPeriodMapper $employeeProjectPeriodMapper,
    ) {
    }

    public function calculate(string $path): array
    {
        $periods = [];
        $invalidRows = 0;

        foreach ($this->csvEmployeeReader->read($path) as $row) {
            try {
                $periods[] = $this->employeeProjectPeriodMapper->map($row);
            } catch (CsvException) {
                $invalidRows++;
            }
        }

        return [
            'periods' => $periods,
            'invalid_rows' => $invalidRows,
        ];
    }
}