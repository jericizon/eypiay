<?php

namespace JericIzon\Eypiay\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseMigrations;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Define environment setup.
     *
     * @param  Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Alter the testing timezone to America/Los_Angeles
        // $app['config']->set('simpletdd.timezone', 'America/Los_Angeles');
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['JericIzon\Eypiay\EypiayServiceProvider'];
    }
}
