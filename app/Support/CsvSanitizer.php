<?php

namespace App\Support;

/**
 * Netralkan "CSV/formula injection": sel teks yang diawali = + - @ (atau tab/CR)
 * diberi awalan petik tunggal agar tidak dieksekusi saat dibuka di spreadsheet.
 */
class CsvSanitizer
{
    public static function cell($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    public static function row(array $row): array
    {
        return array_map([self::class, 'cell'], $row);
    }
}
