<?php

namespace App\Support;

final class YearParameter
{
    public const MAX_DIGITS = 4;

    public static function resolve(mixed $value, int $fallback, int $minYear, int $maxYear): int
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        return self::parse($value, $minYear, $maxYear);
    }

    public static function resolveOrFallback(mixed $value, int $fallback, int $minYear, int $maxYear): int
    {
        try {
            return self::resolve($value, $fallback, $minYear, $maxYear);
        } catch (\InvalidArgumentException) {
            return $fallback;
        }
    }

    public static function parse(mixed $value, int $minYear, int $maxYear): int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (!is_int($value) && !is_string($value)) {
            throw new \InvalidArgumentException('Parameter tahun harus berupa angka.');
        }

        $year = (string) $value;

        if ($year === '' || !ctype_digit($year)) {
            throw new \InvalidArgumentException('Parameter tahun harus berupa angka.');
        }

        if (strlen($year) > self::MAX_DIGITS) {
            throw new \InvalidArgumentException('Parameter tahun maksimal 4 digit.');
        }

        $yearInt = (int) $year;

        if ($yearInt < $minYear || $yearInt > $maxYear) {
            throw new \InvalidArgumentException(sprintf(
                'Parameter tahun harus berada di rentang %d-%d.',
                $minYear,
                $maxYear
            ));
        }

        return $yearInt;
    }
}