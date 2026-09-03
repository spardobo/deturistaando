<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PostgreSQLConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_uses_postgresql_16_with_the_migrated_schema(): void
    {
        $this->assertSame('pgsql', DB::connection()->getDriverName());

        $serverVersionNumber = (int) DB::scalar("select current_setting('server_version_num')");

        $this->assertGreaterThanOrEqual(160000, $serverVersionNumber);
        $this->assertLessThan(170000, $serverVersionNumber);

        $this->assertTrue(Schema::hasTable('migrations'));
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('cache'));
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertTrue(Schema::hasTable('passkeys'));
    }
}
