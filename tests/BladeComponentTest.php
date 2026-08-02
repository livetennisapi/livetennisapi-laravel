<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\Tests;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Blade;

final class BladeComponentTest extends TestCase
{
    public function testRendersLiveMatchesServerSide(): void
    {
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($this->liveMatchesBody())),
        ]);

        $html = Blade::render('<x-tennis-scores />');

        $this->assertStringContainsString('lta-live-scores', $html);
        $this->assertStringContainsString('Lathan Skrobarcek', $html);
        $this->assertStringContainsString('Egor Gorin', $html);
        $this->assertStringContainsString('UTR PTT Waco', $html);
        // score rendered from string points
        $this->assertStringContainsString('30', $html);
        $this->assertStringContainsString('15', $html);
    }

    public function testRendersEmptyStateWhenNoMatches(): void
    {
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => [],
                'meta' => ['limit' => 10, 'offset' => 0, 'count' => 0],
            ])),
        ]);

        $html = Blade::render('<x-tennis-scores status="live" />');

        $this->assertStringContainsString('No live matches', $html);
    }

    public function testApiFailureRendersGracefullyNotFatal(): void
    {
        // A 500 with no retries surfaces a ServerError, which the component must
        // catch and turn into a fallback rather than fataling the page.
        $this->bindFakeClient([
            new Response(500, ['Content-Type' => 'application/json'], json_encode(['error' => 'boom'])),
        ], maxRetries: 0);

        $html = Blade::render('<x-tennis-scores />');

        $this->assertStringContainsString('unavailable', $html);
    }

    public function testUpgradeRequiredRendersVisibleTierNotice(): void
    {
        // status="completed" on a FREE key: the API answers 403 upgrade_required.
        // The component must render a visible upgrade notice naming the required
        // plan, not the generic error fallback and not an empty state.
        $this->bindFakeClient([
            new Response(403, ['Content-Type' => 'application/json'], json_encode([
                'error' => 'upgrade_required',
                'message' => 'Completed-match listings need the BASIC tier ($9.99/mo) or any History plan.',
            ])),
        ], maxRetries: 0);

        $html = Blade::render('<x-tennis-scores status="completed" />');

        $this->assertStringContainsString('lta-scores-upgrade', $html);
        $this->assertStringContainsString('BASIC tier', $html);
        $this->assertStringContainsString('History plan', $html);
        $this->assertStringContainsString('https://livetennisapi.com/subscribe/upgrade', $html);
        $this->assertStringNotContainsString('unavailable', $html);
    }

    public function testAttributesOverrideDefaults(): void
    {
        // status="upcoming" must reach the client; matches with null score
        // render the scheduled fallback, not a fatal.
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    [
                        'id' => 5,
                        'tournament' => 'Roland Garros',
                        'status' => 'upcoming',
                        'players' => [
                            'p1' => ['id' => 1, 'name' => 'A Player'],
                            'p2' => ['id' => 2, 'name' => 'B Player'],
                        ],
                        'score' => null,
                    ],
                ],
                'meta' => ['limit' => 5, 'offset' => 0, 'count' => 1],
            ])),
        ]);

        $html = Blade::render('<x-tennis-scores status="upcoming" :limit="5" />');

        $this->assertStringContainsString('data-status="upcoming"', $html);
        $this->assertStringContainsString('A Player', $html);
        $this->assertStringContainsString('Roland Garros', $html);
    }
}
