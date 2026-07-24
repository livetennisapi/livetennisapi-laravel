<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\Tests;

use GuzzleHttp\Psr7\Response;
use LiveTennisApi\Laravel\Facades\LiveTennis;
use LiveTennisApi\Model\Page;
use LiveTennisApi\Model\TennisMatch;

final class FacadeTest extends TestCase
{
    public function testFacadeProxiesToClientAndDecodes(): void
    {
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($this->liveMatchesBody())),
        ]);

        $page = LiveTennis::listMatches('live');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertCount(1, $page);
        $this->assertInstanceOf(TennisMatch::class, $page[0]);
        $this->assertSame('UTR PTT Waco', $page[0]->tournament);
        // string points survive the round trip through the facade
        $this->assertSame(['30', '15'], $page[0]->score->points);
    }

    public function testFacadeHealth(): void
    {
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'ok', 'version' => 'v1'])),
        ]);

        $this->assertSame(['status' => 'ok', 'version' => 'v1'], LiveTennis::health());
    }
}
