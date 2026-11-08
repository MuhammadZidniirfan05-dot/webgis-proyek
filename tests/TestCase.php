<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Pengaman: tolak menjalankan tes bila bukan database tes
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $config = $app['config'];
        $db = (string) $config->get('database.connections.' . $config->get('database.default') . '.database');

        if (! str_ends_with($db, '_test')) {
            throw new \RuntimeException(
                "Tes dihentikan: database '{$db}' bukan database tes (harus berakhiran _test)."
            );
        }

        return $app;
    }
}