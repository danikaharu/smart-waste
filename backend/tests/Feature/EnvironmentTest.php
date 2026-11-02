<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    public function test_database_connection_and_environment(): void
    {
        $pdo = DB::connection()->getPdo();
        $this->assertNotNull($pdo);
        $this->assertEquals('smart_waste_db', DB::connection()->getDatabaseName());
    }
}
