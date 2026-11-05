<?php

namespace App\Support\Reports;

use Illuminate\Support\Str;

class SpreadsheetCell
{
    public static function text(?string $value): string
    {
        $value ??= '';

        if (Str::startsWith($value, ['=', '+', '-', '@', "\t", "\r"])) {
            return "'{$value}";
        }

        return $value;
    }
}
