<?php

namespace App\Services;

class EmployeePairService
{
    public function __construct(
        private CsvEmployeeReader $csvEmployeeReader,
    ) {
    }

    public function calculate(string $path): array
    {
        $rows = [];

        foreach ($this->csvEmployeeReader->read($path) as $row) {
            $rows[] = $row;
        }

        return $rows;
    }
}