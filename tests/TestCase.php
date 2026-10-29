<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutViteAssets();
    }

    /**
     * Render pages without a Vite build. withoutVite() does not stub @fonts, so both are stubbed here.
     */
    protected function withoutViteAssets(): void
    {
        $this->swap(Vite::class, new class extends Vite
        {
            public function __invoke($entrypoints, $buildDirectory = null)
            {
                return new HtmlString('');
            }

            public function fonts($aliases = null)
            {
                return new HtmlString('');
            }
        });
    }
}
