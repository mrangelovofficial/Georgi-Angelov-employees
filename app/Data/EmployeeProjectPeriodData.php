<?php
namespace App\Data;

use Carbon\CarbonImmutable;

final readonly class EmployeeProjectPeriodData
{
    public function __construct(
        public int $employeeId,
        public int $projectId,
        public CarbonImmutable $dateFrom,
        public CarbonImmutable $dateTo,
    ) {}
}