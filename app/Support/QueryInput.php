<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Membaca parameter query sebagai string yang aman. `?kampus[]=x` menghasilkan array, dan
 * `(string) $array` melempar "Array to string conversion" (500). Nilai selain string dianggap kosong.
 */
class QueryInput
{
    public static function string(Request $request, string $key, string $default = ''): string
    {
        $value = $request->input($key, $default);

        return is_string($value) ? trim($value) : $default;
    }
}
