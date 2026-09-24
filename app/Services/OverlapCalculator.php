<?php

namespace App\Services;

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

            $overlapStart = $currentPeriod->dateFrom->greaterThan($comparedPeriod->dateFrom)
                ? $currentPeriod->dateFrom
                : $comparedPeriod->dateFrom;

            $overlapEnd = $currentPeriod->dateTo->lessThan($comparedPeriod->dateTo)
                ? $currentPeriod->dateTo
                : $comparedPeriod->dateTo;

            if ($overlapStart->lessThanOrEqualTo($overlapEnd)) {
                $totalOverlapDays += $overlapStart->diffInDays($overlapEnd) + 1;
            }

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
}
