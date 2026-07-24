<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\Tests;

use Illuminate\Support\Facades\Blade;
use LiveTennisApi\Laravel\Facades\LiveTennis;
use LiveTennisApi\LiveTennisApi;

/**
 * End-to-end smoke against the REAL API through the full Laravel stack
 * (provider -> configured client -> facade -> Blade component).
 *
 * Skipped unless LIVETENNISAPI_KEY is set, so the normal suite stays offline.
 * Run: LIVETENNISAPI_KEY=twjp_… vendor/bin/phpunit --filter LiveSmokeTest
 */
final class LiveSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $key = getenv('LIVETENNISAPI_KEY');
        if ($key === false || $key === '') {
            $this->markTestSkipped('LIVETENNISAPI_KEY not set — skipping live smoke.');
        }

        config()->set('livetennis.key', $key);
        $this->app->forgetInstance(LiveTennisApi::class);
    }

    public function testFacadeHitsLiveApi(): void
    {
        $health = LiveTennis::health();
        $this->assertSame('ok', $health['status'] ?? null);

        $live = LiveTennis::listMatches('live', limit: 5);
        fwrite(STDERR, "\n[live] facade listMatches('live') -> " . count($live) . " matches\n");
        $this->assertGreaterThanOrEqual(0, count($live));
    }

    public function testBladeComponentRendersLiveApi(): void
    {
        $html = Blade::render('<x-tennis-scores :limit="5" />');
        fwrite(STDERR, "[live] <x-tennis-scores /> rendered " . strlen($html) . " bytes\n");
        $this->assertStringContainsString('lta-live-scores', $html);
        // Must never fatal, even if the API returns an error/empty.
        $this->assertStringNotContainsString('Fatal error', $html);
    }
}
