<?php

namespace App\Services;

use App\Exceptions\CsvException;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;

class DateParser
{
    private const FORMATS = [
        'Y-m-d',
        'Y/m/d',
        'd-m-Y',
        'd/m/Y',
        'd.m.Y',
    ];

    public function parse(string $value): CarbonImmutable
    {
        $value = trim($value);

        if ($value === '') {
            throw new CsvException('Date cannot be empty.');
        }

        foreach (self::FORMATS as $format) {
            try {
                $date = CarbonImmutable::createFromFormat('!'.$format, $value);

                if ($date->format($format) === $value) {
                    return $date;
                }
            } catch (InvalidFormatException) {
                continue;
            }
        }

        throw new CsvException("Invalid date format: {$value}.");
    }

    public function parseNullable(?string $value): CarbonImmutable
    {
        if ($value === null || strcasecmp(trim($value), 'NULL') === 0) {
            return CarbonImmutable::today();
        }

        return $this->parse($value);
    }
}
