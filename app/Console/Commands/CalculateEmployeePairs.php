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

        return self::SUCCESS;
    }
}
