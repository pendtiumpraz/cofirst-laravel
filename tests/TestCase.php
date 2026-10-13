<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Render views without a Vite dev server or build manifest.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
