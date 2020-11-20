<?php

namespace Expanse\ApiGuard\Tests;

use Expanse\ApiGuard\Models\ApiKey;
use Expanse\ApiGuard\Models\Mixins\Apikeyable;
use Illuminate\Http\Request;

class CanGuardRouteTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../database/migrations'),
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('apiguard.header_key', 'X-Authorization');
        $app['config']->set('apiguard.models', [ 'api_key' => ApiKey::class ]);
    }

    public function test_no_apikeys_triggers_unauthorized()
    {
        $this->app['router']
             ->middleware('auth.apikey')
             ->get(__FUNCTION__, ['uses' => function () {
                 return 'hello world';
             }]);

        $response = $this->get(__FUNCTION__ . '?foo=bar');

        $response->assertStatus(401);
        $error = $response->json();

        $this->assertSame(data_get($error, 'error.code'), '401');
        $this->assertSame(data_get($error, 'error.http_code'), "GEN-UNAUTHORIZED");
        $this->assertSame(data_get($error, 'error.message'), "Unauthorized.");
    }

    public function test_auth_request_returns_proper_value()
    {
        // Set up a user
        $user = new CanGuardRouteTestUser();
        $user->forceFill([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'test@example.com',
        ]);
        $user->save();
        $api_key = ApiKey::make($user);
        $api_key->save();

        // Define the route to hit
        $this->app['router']
             ->middleware('auth.apikey')
             ->get(__FUNCTION__, ['uses' => function (Request $request) {
                 return json_encode($request->user());
             }]);

        // Hit the route
        $response = $this
            ->withHeader('X-Authorization', $api_key->key)
            ->get(__FUNCTION__ . '?foo=bar');

        $response->assertStatus(200);

        $responseUser = $response->json();

        $this->assertSame(data_get($responseUser, 'id'), $user->id);
    }

    public function test_auth_request_with_invalid_key_returns_unauthorized()
    {
        // Set up a user
        $user = new CanGuardRouteTestUser();
        $user->forceFill([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'test@example.com',
        ]);
        $user->save();
        $api_key = ApiKey::make($user);
        $api_key->save();

        // Define the route to hit
        $this->app['router']
             ->middleware('auth.apikey')
             ->get(__FUNCTION__, ['uses' => function (Request $request) {
                 return json_encode($request->user());
             }]);

        // Hit the route
        $response = $this
            ->withHeader('X-Authorization', $api_key->key . '1')
            ->get(__FUNCTION__ . '?foo=bar');

        $response->assertStatus(401);

        $error = $response->json();

        $this->assertSame(data_get($error, 'error.code'), '401');
        $this->assertSame(data_get($error, 'error.http_code'), "GEN-UNAUTHORIZED");
        $this->assertSame(data_get($error, 'error.message'), "Unauthorized.");
    }
}

/**
 * Create a base User class here for test purposes
 */
class CanGuardRouteTestUser extends \Illuminate\Database\Eloquent\Model {
    use Apikeyable;

    protected $table = 'users';
}
