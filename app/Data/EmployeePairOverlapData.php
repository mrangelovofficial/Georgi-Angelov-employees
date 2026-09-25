<?php

namespace App\Data;

final readonly class EmployeePairOverlapData
{
    public function __construct(
        public int $employee1Id,
        public int $employee2Id,
        public int $projectId,
        public int $daysWorkedTogether,
    ) {}
}
