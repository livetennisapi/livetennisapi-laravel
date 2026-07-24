<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\Tests;

use LiveTennisApi\Laravel\Facades\LiveTennis;
use LiveTennisApi\LiveTennisApi;

final class ServiceProviderTest extends TestCase
{
    public function testClientResolvesAsSingleton(): void
    {
        $a = $this->app->make(LiveTennisApi::class);
        $b = $this->app->make(LiveTennisApi::class);

        $this->assertInstanceOf(LiveTennisApi::class, $a);
        $this->assertSame($a, $b, 'client must be a singleton');
    }

    public function testConfigIsMergedWithDefaults(): void
    {
        $this->assertSame(
            'https://api.livetennisapi.com/api/public/v1',
            config('livetennis.base_url'),
        );
        $this->assertSame('live', config('livetennis.scores.status'));
        $this->assertSame(10, config('livetennis.scores.limit'));
    }

    public function testKeyIsReadFromConfig(): void
    {
        config()->set('livetennis.key', 'twjp_from_config');
        // Rebuild the singleton so it picks up the new config.
        $this->app->forgetInstance(LiveTennisApi::class);

        $client = $this->app->make(LiveTennisApi::class);
        $this->assertInstanceOf(LiveTennisApi::class, $client);
    }

    public function testFacadeResolvesTheContainerClient(): void
    {
        $bound = $this->bindFakeClient([]);
        $this->assertSame($bound, LiveTennis::getFacadeRoot());
    }

    public function testAliasBinding(): void
    {
        $this->assertInstanceOf(LiveTennisApi::class, $this->app->make('livetennis'));
    }
}
