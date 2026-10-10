<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Ambil nilai rahasia. Nilai baru disimpan terenkripsi (awalan "enc:");
     * nilai lama berupa plaintext tetap terbaca sampai disimpan ulang.
     */
    public static function getSecret(string $key, $default = null)
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        if (str_starts_with($value, 'enc:')) {
            try {
                return \Illuminate\Support\Facades\Crypt::decryptString(substr($value, 4));
            } catch (\Throwable $e) {
                return $default;
            }
        }

        return $value;
    }

    public static function setSecret(string $key, ?string $value)
    {
        return self::set(
            $key,
            ($value === null || $value === '') ? null : 'enc:' . \Illuminate\Support\Facades\Crypt::encryptString($value)
        );
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
