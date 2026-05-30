<?php

namespace Aghfatehi\Tabby\Tests;

use Aghfatehi\Tabby\Services\TabbyService;
use Orchestra\Testbench\TestCase;

class TabbyServiceTest extends TestCase
{
    protected TabbyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TabbyService();
    }

    /** @test */
    public function it_resolves_sandbox_base_url()
    {
        config(['tabby.sandbox' => true]);
        $url = $this->service->baseUrl();

        $this->assertEquals('https://api.tabby.ai', $url);
    }

    /** @test */
    public function it_resolves_saudi_production_url()
    {
        config(['tabby.sandbox' => false]);
        config(['tabby.region' => 'sa']);
        $url = $this->service->baseUrl();

        $this->assertEquals('https://api.tabby.sa', $url);
    }

    /** @test */
    public function it_resolves_uae_production_url()
    {
        config(['tabby.sandbox' => false]);
        config(['tabby.region' => 'ae']);
        $url = $this->service->baseUrl();

        $this->assertEquals('https://api.tabby.ai', $url);
    }

    /** @test */
    public function it_resolves_kuwait_production_url()
    {
        config(['tabby.sandbox' => false]);
        config(['tabby.region' => 'kw']);
        $url = $this->service->baseUrl();

        $this->assertEquals('https://api.tabby.ai', $url);
    }

    /** @test */
    public function it_formats_amount_correctly()
    {
        $this->assertEquals('100.00', TabbyService::class);
    }
}
