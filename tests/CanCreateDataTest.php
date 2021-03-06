<?php

namespace Expanse\ApiGuard\Tests;

use Expanse\ApiGuard\Models\ApiKey;

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

    public function test_can_create_model()
    {
        $user = new CanCreateDataTestUser();
        $api_key = ApiKey::make($user);

        $this->assertSame(1, $api_key->id);
    }
}

/**
 * Create a base User class here for test purposes
 */
class CanCreateDataTestUser extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'users';

}
