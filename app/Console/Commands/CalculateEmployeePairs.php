<?php

namespace App\Console\Commands;

use App\Services\EmployeePairService;
use Illuminate\Console\Command;

class CalculateEmployeePairs extends Command
{
    protected $signature = 'employees:calculate
                            {file}
                            {--all : Show all results}';

    protected $description = 'Calculate employee pairs from a CSV file';

    private const HEADERS = [
        'Employee ID #1',
        'Employee ID #2',
        'Project ID',
        'Days Worked Together',
    ];

    public function handle(EmployeePairService $employeePairService): int
    {
        $path = $this->argument('file');

        if (! is_file($path)) {
            $this->error('File not found.');

            return self::FAILURE;
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $result = $employeePairService->calculate($path);

        $rows = $result['results'];

        if (count($rows) === 0) {
            $this->info('No employee pairs found.');

            return self::SUCCESS;
        }

        if ($this->option('all')) {
            $batch = [];

            foreach ($rows as $row) {
                $batch[] = $row;

                if (count($batch) === 1000) {
                    $this->table(self::HEADERS, $batch);
                    $batch = [];
                }
            }

            if ($batch !== []) {
                $this->table(self::HEADERS, $batch);
            }
        } else {
            $this->table(self::HEADERS, $rows->page(limit: 20)['rows']);
        }

        $this->newLine();

        $this->line('Invalid rows: '.$result['invalid_rows']);

        if (! $this->option('all') && count($result['results']) > 20) {
            $this->line('Showing first 20 rows. Use --all to show everything.');
        }

        $executionTime = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;
        $peakMemory = memory_get_peak_usage(true);

        $this->line('Execution time: '.round($executionTime, 3).' seconds');
        $this->line('Memory used: '.round($memoryUsed / 1024 / 1024, 2).' MB');
        $this->line('Peak memory: '.round($peakMemory / 1024 / 1024, 2).' MB');

        return self::SUCCESS;
    }
}
