<?php

namespace App\Services;

use App\Data\EmployeeProjectPeriodData;
use Generator;
use RuntimeException;

class ProjectPeriodStore
{
    private const BUFFER_SIZE = 8 * 1024 * 1024;

    private array $files = [];

    private array $buffers = [];

    private int $bufferedBytes = 0;

    public function __construct(
        private CsvEmployeeReader $csvEmployeeReader,
        private EmployeeProjectPeriodMapper $employeeProjectPeriodMapper,
    ) {}

    public function add(EmployeeProjectPeriodData $period): void
    {
        if (! isset($this->files[$period->projectId])) {
            $path = tempnam(sys_get_temp_dir(), 'employee-project-');

            if ($path === false) {
                throw new RuntimeException('Unable to create a temporary project file.');
            }

            $this->files[$period->projectId] = $path;
        }

        $line = implode(',', [
            $period->employeeId,
            $period->projectId,
            $period->dateFrom->format('Y-m-d'),
            $period->dateTo->format('Y-m-d'),
        ])."\n";

        $this->buffers[$period->projectId] ??= '';
        $this->buffers[$period->projectId] .= $line;
        $this->bufferedBytes += strlen($line);

        if ($this->bufferedBytes >= self::BUFFER_SIZE) {
            $this->flush();
        }
    }

    public function projects(): Generator
    {
        $this->flush();

        foreach ($this->files as $projectId => $path) {
            $employees = [];

            foreach ($this->csvEmployeeReader->read($path) as $row) {
                $period = $this->employeeProjectPeriodMapper->map($row);
                $employees[$period->employeeId][] = $period;
            }

            yield $employees;
        }
    }

    public function clear(): void
    {
        foreach ($this->files as $path) {
            unlink($path);
        }

        $this->files = [];
        $this->buffers = [];
        $this->bufferedBytes = 0;
    }

    private function flush(): void
    {
        foreach ($this->buffers as $projectId => $buffer) {
            if (file_put_contents($this->files[$projectId], $buffer, FILE_APPEND) !== strlen($buffer)) {
                throw new RuntimeException('Unable to write a temporary project file.');
            }
        }

        $this->buffers = [];
        $this->bufferedBytes = 0;
    }
}
