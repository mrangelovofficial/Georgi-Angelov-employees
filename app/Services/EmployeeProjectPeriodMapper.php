<?php

namespace App\Services;

use App\Data\EmployeeProjectPeriodData;
use App\Exceptions\CsvException;

class EmployeeProjectPeriodMapper
{
    public function __construct(
        private DateParser $dateParser,
    ) {}

    public function map(array $row): EmployeeProjectPeriodData
    {
        if (count($row) !== 4) {
            throw new CsvException('Invalid number of columns.');
        }

        $dateFrom = $this->dateParser->parse($row[2]);
        $dateTo = $this->dateParser->parseNullable($row[3]);

        if ($dateFrom->greaterThan($dateTo)) {
            throw new CsvException('DateFrom cannot be after DateTo.');
        }

        return new EmployeeProjectPeriodData(
            employeeId: $this->parseId($row[0], 'Employee ID'),
            projectId: $this->parseId($row[1], 'Project ID'),
            dateFrom: $dateFrom,
            dateTo: $dateTo,
        );
    }

    private function parseId(string $value, string $field): int
    {
        if ($value === '' || ! ctype_digit($value)) {
            throw new CsvException("{$field} must be a valid integer.");
        }

        return (int) $value;
    }
}
