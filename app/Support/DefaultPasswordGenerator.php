<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Throwable;

class DefaultPasswordGenerator
{
    /**
     * Generate default password for student based on birth date and LRN:
     * Format: MMDDYYYY + last 4 digits of LRN.
     */
    public static function forStudent(string|DateTimeInterface|null $birthDate, string $lrn): string
    {
        return static::generate($birthDate, $lrn);
    }

    /**
     * Generate default password for staff based on birth date and employee number:
     * Format: MMDDYYYY + last 4 digits of employee number.
     */
    public static function forStaff(string|DateTimeInterface|null $birthDate, string $employeeNo): string
    {
        return static::generate($birthDate, $employeeNo);
    }

    /**
     * Core generator formula: MMDDYYYY + last 4 digits of credential.
     */
    public static function generate(string|DateTimeInterface|null $birthDate, string $credential): string
    {
        if (empty($birthDate)) {
            throw new InvalidArgumentException('Birth date is required to generate default password.');
        }

        try {
            $parsedDate = $birthDate instanceof DateTimeInterface
                ? Carbon::instance($birthDate)
                : Carbon::parse((string) $birthDate);
        } catch (Throwable $e) {
            throw new InvalidArgumentException("Invalid birth date provided: {$e->getMessage()}", previous: $e);
        }

        $datePart = $parsedDate->format('mdY');
        $credPart = strlen($credential) <= 4 ? $credential : substr($credential, -4);

        return $datePart.$credPart;
    }
}
