<?php

namespace App\Services;

use Generator;
use RuntimeException;

class CsvEmployeeReader
{
    private const HEADER = [
        'EmpID',
        'ProjectID',
        'DateFrom',
        'DateTo',
    ];

    public function read(string $path): Generator
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to read the CSV file.');
        }

        try {
            $firstRow = $this->readRow($handle);

            if ($firstRow === null) {
                return;
            }

            if (! $this->isHeader($firstRow)) {
                yield $firstRow;
            }

            while (($row = $this->readRow($handle)) !== null) {
                yield $row;
            }
        } finally {
            fclose($handle);
        }
    }

    private function readRow($handle): ?array
    {
        while (($row = fgetcsv($handle)) !== false) {
            $row = $this->cleanRow($row);

            if ($this->isEmptyRow($row)) {
                continue;
            }

            return $row;
        }

        return null;
    }

    private function isHeader(array $row): bool
    {
        return array_map('strtolower', $row) ===
            array_map('strtolower', self::HEADER);
    }

    private function cleanRow(array $row): array
    {
        $row = array_map('trim', $row);

        if (isset($row[0])) {
            $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
        }

        return $row;
    }

    private function isEmptyRow(array $row): bool
    {
        return count(array_filter($row, fn ($value) => $value !== '')) === 0;
    }
}