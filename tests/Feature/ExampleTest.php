<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL sends visitors to the client portal.
     */
    public function test_the_root_url_redirects_to_the_portal(): void
    {
        $this->get('/')->assertRedirect('/portal');
    }
}
