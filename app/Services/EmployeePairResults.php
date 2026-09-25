<?php

namespace App\Services;

use App\Data\EmployeePairOverlapData;
use Countable;
use Generator;
use IteratorAggregate;
use RuntimeException;

class EmployeePairResults implements Countable, IteratorAggregate
{
    private const BUFFER_SIZE = 8 * 1024 * 1024;

    private array $buffers = [];

    private int $bufferedBytes = 0;

    private int $count = 0;

    private function __construct(private string $directory) {}

    public static function create(): self
    {
        $root = sys_get_temp_dir().'/employee-pair-results';

        if (! is_dir($root) && ! mkdir($root, 0700, true) && ! is_dir($root)) {
            throw new RuntimeException('Unable to create the results directory.');
        }

        $directory = $root.'/'.bin2hex(random_bytes(16));

        if (! mkdir($directory, 0700)) {
            throw new RuntimeException('Unable to create a temporary results directory.');
        }

        return new self($directory);
    }

    public function add(EmployeePairOverlapData $result): void
    {
        $days = $result->daysWorkedTogether;

        $line = implode(',', [
            $result->employee1Id,
            $result->employee2Id,
            $result->projectId,
            $days,
        ])."\n";

        $this->buffers[$days] ??= '';
        $this->buffers[$days] .= $line;

        $this->bufferedBytes += strlen($line);
        $this->count++;

        if ($this->bufferedBytes >= self::BUFFER_SIZE) {
            $this->flush();
        }
    }

    public function count(): int
    {
        return $this->count;
    }

    public function getIterator(): Generator
    {
        yield from $this->read();
    }

    public function take(int $limit): array
    {
        $results = [];

        foreach ($this->read() as $result) {
            if (count($results) >= $limit) {
                break;
            }

            $results[] = $result;
        }

        return $results;
    }

    public function clear(): void
    {
        $this->buffers = [];
        $this->bufferedBytes = 0;
        $this->count = 0;

        if (is_dir($this->directory)) {
            self::removeDirectory($this->directory);
        }
    }

    public function __destruct()
    {
        $this->clear();
    }

    public function flush(): void
    {
        foreach ($this->buffers as $days => $buffer) {
            if (
                file_put_contents(
                    $this->directory.'/'.$days.'.csv',
                    $buffer,
                    FILE_APPEND
                ) !== strlen($buffer)
            ) {
                throw new RuntimeException('Unable to write employee pair results.');
            }
        }

        $this->buffers = [];
        $this->bufferedBytes = 0;
    }

    private function read(): Generator
    {
        $this->flush();

        $files = [];

        foreach (glob($this->directory.'/*.csv') ?: [] as $path) {
            $files[(int) basename($path, '.csv')] = $path;
        }

        krsort($files, SORT_NUMERIC);

        foreach ($files as $path) {
            $handle = fopen($path, 'r');

            if ($handle === false) {
                throw new RuntimeException('Unable to read employee pair results.');
            }

            try {
                while (($line = fgets($handle)) !== false) {
                    $row = array_map(
                        'intval',
                        explode(',', trim($line))
                    );

                    if (count($row) !== 4) {
                        throw new RuntimeException(
                            'Invalid employee pair result.'
                        );
                    }

                    yield new EmployeePairOverlapData(
                        employee1Id: $row[0],
                        employee2Id: $row[1],
                        projectId: $row[2],
                        daysWorkedTogether: $row[3],
                    );
                }
            } finally {
                fclose($handle);
            }
        }
    }

    private static function removeDirectory(string $directory): void
    {
        foreach (glob($directory.'/*.csv') ?: [] as $path) {
            unlink($path);
        }

        rmdir($directory);
    }
}
