<?php

namespace App\Services;

use App\Data\EmployeeProjectPeriodData;

class OverlapCalculator
{
    public function calculate(
        array $currentEmployeePeriods,
        array $comparedEmployeePeriods
    ): int {
        $totalOverlapDays = 0;
        $currentPeriodIndex = 0;
        $comparedPeriodIndex = 0;

        while (
            isset($currentEmployeePeriods[$currentPeriodIndex]) &&
            isset($comparedEmployeePeriods[$comparedPeriodIndex])
        ) {
            $currentPeriod = $currentEmployeePeriods[$currentPeriodIndex];
            $comparedPeriod = $comparedEmployeePeriods[$comparedPeriodIndex];

            $totalOverlapDays += $this->calculatePeriodOverlap($currentPeriod, $comparedPeriod);

            if ($currentPeriod->dateTo->equalTo($comparedPeriod->dateTo)) {
                $currentPeriodIndex++;
                $comparedPeriodIndex++;
            } elseif ($currentPeriod->dateTo->lessThan($comparedPeriod->dateTo)) {
                $currentPeriodIndex++;
            } else {
                $comparedPeriodIndex++;
            }
        }

        return $totalOverlapDays;
    }

    public function calculatePeriodOverlap(
        EmployeeProjectPeriodData $first,
        EmployeeProjectPeriodData $second,
    ): int {
        $start = max($first->dateFrom, $second->dateFrom);
        $end = min($first->dateTo, $second->dateTo);

        return $start <= $end ? date_diff($start, $end)->days + 1 : 0;
    }
}
