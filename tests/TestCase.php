<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use LiveTennisApi\Laravel\Facades\LiveTennis;
use LiveTennisApi\Laravel\LiveTennisServiceProvider;
use LiveTennisApi\LiveTennisApi;
use Orchestra\Testbench\TestCase as Orchestra;
use Psr\Http\Message\ResponseInterface;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [LiveTennisServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['LiveTennis' => LiveTennis::class];
    }

    /**
     * Build a client whose transport replays canned Guzzle responses, so tests
     * never touch the network. Guzzle's Client is itself a PSR-18 client.
     *
     * @param array<int, ResponseInterface|\Throwable> $responses
     */
    protected function fakeClient(array $responses, int $maxRetries = 0): LiveTennisApi
    {
        $stack = HandlerStack::create(new MockHandler($responses));

        return new LiveTennisApi('twjp_test', [
            'http_client' => new GuzzleClient(['handler' => $stack]),
            'max_retries' => $maxRetries,
        ]);
    }

    /**
     * Bind a fake client into the container in place of the configured one.
     *
     * @param array<int, ResponseInterface|\Throwable> $responses
     */
    protected function bindFakeClient(array $responses, int $maxRetries = 0): LiveTennisApi
    {
        $client = $this->fakeClient($responses, $maxRetries);
        $this->app->instance(LiveTennisApi::class, $client);

        return $client;
    }

    /**
     * A minimal but realistic live-match payload (string points, per-set games,
     * int server) matching the verified live shape.
     *
     * @return array<string, mixed>
     */
    protected function liveMatchesBody(): array
    {
        return [
            'data' => [
                [
                    'id' => 22313,
                    'tournament' => 'UTR PTT Waco',
                    'status' => 'live',
                    'is_doubles' => false,
                    'players' => [
                        'p1' => ['id' => 1, 'name' => 'Lathan Skrobarcek', 'tour' => 'atp'],
                        'p2' => ['id' => 2, 'name' => 'Egor Gorin', 'tour' => 'atp'],
                    ],
                    'score' => [
                        'sets' => [1, 1],
                        'games' => [[4, 6, 4], [6, 2, 2]],
                        'points' => ['30', '15'],
                        'server' => 1,
                        'is_tiebreak' => false,
                    ],
                ],
            ],
            'meta' => ['limit' => 10, 'offset' => 0, 'count' => 1],
        ];
    }
}
