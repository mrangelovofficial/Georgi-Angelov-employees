<?php

namespace App\Services;

use App\Data\EmployeePairOverlapData;
use App\Data\EmployeeProjectPeriodData;
use Generator;

class ProjectPairCalculator
{
    public function __construct(
        private IntervalMerger $intervalMerger,
        private OverlapCalculator $overlapCalculator,
    ) {}

    public function calculate(array $employees): Generator
    {
        $periods = [];
        $projectId = null;

        foreach ($employees as $employeePeriods) {
            foreach ($this->intervalMerger->merge($employeePeriods) as $period) {
                $periods[] = $period;
                if ($projectId === null) {
                    $projectId = $period->projectId;
                }
            }
        }
        usort($periods, fn (EmployeeProjectPeriodData $a, EmployeeProjectPeriodData $b) => $a->dateFrom <=> $b->dateFrom);

        $overlappingCandidates = [];
        $overlaps = [];
        foreach ($periods as $index => $currentPeriod) {
            foreach ($overlappingCandidates as $candidateIndex => $comparedPeriod) {
                if ($comparedPeriod->dateTo < $currentPeriod->dateFrom) {
                    unset($overlappingCandidates[$candidateIndex]);

                    continue;
                }

                if ($comparedPeriod->employeeId === $currentPeriod->employeeId) {
                    continue;
                }

                $currentEmployeeId = min(
                    $currentPeriod->employeeId,
                    $comparedPeriod->employeeId
                );

                $comparedEmployeeId = max(
                    $currentPeriod->employeeId,
                    $comparedPeriod->employeeId
                );

                $overlaps[$currentEmployeeId][$comparedEmployeeId] ??= 0;

                $overlaps[$currentEmployeeId][$comparedEmployeeId] +=
                    $this->overlapCalculator->calculatePeriodOverlap(
                        $currentPeriod,
                        $comparedPeriod
                    );
            }

            $overlappingCandidates[$index] = $currentPeriod;
        }

        foreach ($overlaps as $coworkerId => $coworkers) {
            foreach ($coworkers as $otherCoworkerId => $days) {
                yield new EmployeePairOverlapData(
                    employee1Id: $coworkerId,
                    employee2Id: $otherCoworkerId,
                    projectId: $projectId,
                    daysWorkedTogether: $days,
                );
            }
        }
    }
}
