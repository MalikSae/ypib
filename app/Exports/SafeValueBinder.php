<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Basis export: teks yang diawali "=" ditulis sebagai string, bukan formula,
 * sehingga data isian pengguna (nama, alamat, dll.) tidak bisa dieksekusi
 * saat berkas dibuka di Excel. Deretan digit sepanjang >= 15 (mis. NIK 16 digit,
 * nomor rekening) juga ditulis sebagai teks karena Excel hanya menyimpan 15 digit
 * signifikan dan akan membulatkan digit terakhir menjadi 0.
 */
class SafeValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, $value = null)
    {
        if (is_string($value) && str_starts_with($value, '=')) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        if (is_string($value) && strlen($value) >= 15 && ctype_digit($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
