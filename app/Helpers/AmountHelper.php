<?php
declare(strict_types=1);

namespace App\Helpers;

final readonly class AmountHelper
{
    public static function format(string $number, int $precision = 2): string
    {
        if (!str_contains($number, '.')) {
            return $number;
        }

        if ($number[0] !== '-') {
            $rounded = bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        } else {
            $rounded = bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        $rounded = rtrim($rounded, '0');
        $rounded = rtrim($rounded, '.');

        return $rounded;
    }
}
