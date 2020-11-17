<?php

namespace Chrisbjr\ApiGuard\Tests;

class CanCreateDataTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testbench']);

        $this->loadMigrationsFrom([
            '--database' => 'testbench',
            '--path' => realpath(__DIR__.'/../database/migrations'),
        ]);

    }

    public function test_runs_migrations()
    {
        $records = \DB::table('api_keys')->count();
        $this->assertSame(0, $records);
    }
}
