<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pengaman: RefreshDatabase menghapus seluruh tabel. Jangan pernah jalan di
        // database dev/produksi. Atur DB_DATABASE (berakhiran _test) di phpunit.xml.
        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (!str_ends_with($database, '_test') && $database !== ':memory:') {
            throw new \RuntimeException(
                "Test menolak berjalan di database '{$database}'. Gunakan database khusus test (nama berakhiran _test), "
                . 'mis. ypib_test, lewat phpunit.xml.'
            );
        }
    }
}
