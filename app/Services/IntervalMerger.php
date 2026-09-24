<?php

namespace App\Services;

use App\Data\EmployeeProjectPeriodData;

class IntervalMerger
{
    public function merge(array $periods): array
    {
        if (count($periods) <= 1) {
            return $periods;
        }

        usort(
            $periods,
            fn (
                EmployeeProjectPeriodData $a,
                EmployeeProjectPeriodData $b
            ) => $a->dateFrom <=> $b->dateFrom
        );

        $merged = [array_shift($periods)];
        foreach ($periods as $period) {
            $lastIndex = array_key_last($merged);
            $last = $merged[$lastIndex];

            if ($period->dateFrom->greaterThan($last->dateTo)) {
                $merged[] = $period;

                continue;
            }

            if ($period->dateTo->greaterThan($last->dateTo)) {
                $merged[$lastIndex] = new EmployeeProjectPeriodData(
                    employeeId: $last->employeeId,
                    projectId: $last->projectId,
                    dateFrom: $last->dateFrom,
                    dateTo: $period->dateTo,
                );
            }
        }

        return $merged;
    }
}
