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

        if (empty($rows)) {
            $this->info('No employee pairs found.');

            return self::SUCCESS;
        }

        if (! $this->option('all')) {
            $rows = array_slice($rows, 0, 20);
        }

        $this->table(
            [
                'Employee ID #1',
                'Employee ID #2',
                'Project ID',
                'Days worked together',
            ],
            $rows
        );

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
