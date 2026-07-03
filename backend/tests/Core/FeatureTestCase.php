<?php

declare(strict_types=1);

namespace Tests\Core;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Boot helper for test runs.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
