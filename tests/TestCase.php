<?php

namespace Eypiay\Eypiay\Tests;

use Carbon\Carbon;
use Eypiay\Eypiay\EypiayServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use DB;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
  public function setUp(): void
  {
    parent::setUp();
    $this->migrateDatabase();
    $this->seedDatabase();
  }

  protected function migrateDatabase()
  {
    /** @var \Illuminate\Database\Schema\Builder $schemaBuilder */
    $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();
    if (!$schemaBuilder->hasTable('users')) {
      $schemaBuilder->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
      });
    }
  }

  protected function seedDatabase()
  {
    for ($i = 0; $i <= 150; $i++) {
      $name = 'user_' . Str::random(5);
      DB::table('users')
        ->insert([
          'name' => $name,
          'email' => Str::slug($name) . '@example.com',
          'email_verified_at' => Carbon::now(),
          'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
          'remember_token' => Str::random(10),
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);
    }
  }

  protected function getPackageProviders($app)
  {
    return [
      EypiayServiceProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    // perform environment setup
    $app['config']->set('app.debug', true);
    $app['config']->set('database.default', 'testdb');
    $app['config']->set('database.connections.testdb', [
      'driver'   => 'sqlite',
      'database' => ':memory:',
      'prefix'   => '',
    ]);
  }
}
