<?php

namespace Aghfatehi\Tabby\Tests;

use Orchestra\Testbench\TestCase;

class TabbyControllerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Aghfatehi\Tabby\TabbyServiceProvider::class,
        ];
    }

    /** @test */
    public function it_can_access_cancel_route()
    {
        $response = $this->get(route('tabby.cancel'));
        $response->assertStatus(302);
    }

    /** @test */
    public function it_can_access_failure_route()
    {
        $response = $this->get(route('tabby.failure'));
        $response->assertStatus(302);
    }
}
