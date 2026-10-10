<?php

namespace App\Support;

/**
 * Menyaring HTML yang diketik admin (mis. deskripsi prodi) sebelum disimpan
 * dan sebelum ditampilkan di halaman publik. Memakai HTMLPurifier dengan
 * daftar putih tag/atribut/properti CSS; skrip, handler on*, dan URL
 * `javascript:` dibuang.
 */
class HtmlSanitizer
{
    private static ?\HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        return self::purifier()->purify($html);
    }

    private static function purifier(): \HTMLPurifier
    {
        if (self::$purifier === null) {
            $cachePath = storage_path('framework/cache/htmlpurifier');
            if (!is_dir($cachePath)) {
                @mkdir($cachePath, 0775, true);
            }

            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
            $config->set('HTML.AllowedElements', [
                'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'span', 'div',
                'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'h5', 'blockquote', 'a',
                'table', 'thead', 'tbody', 'tr', 'th', 'td',
            ]);
            $config->set('HTML.AllowedAttributes', [
                'a.href', 'a.title', 'a.target', '*.class', '*.style',
                'td.colspan', 'td.rowspan', 'th.colspan', 'th.rowspan',
            ]);
            $config->set('CSS.AllowedProperties', [
                'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
                'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right',
                'font-weight', 'font-style', 'text-align', 'line-height', 'list-style-type',
                'text-decoration', 'color',
            ]);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
            $config->set('HTML.TargetBlank', true);
            $config->set('HTML.TargetNoopener', true);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);

            if (is_dir($cachePath) && is_writable($cachePath)) {
                $config->set('Cache.SerializerPath', $cachePath);
            } else {
                $config->set('Cache.DefinitionImpl', null);
            }

            self::$purifier = new \HTMLPurifier($config);
        }

        return self::$purifier;
    }
}
