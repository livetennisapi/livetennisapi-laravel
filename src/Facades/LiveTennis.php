<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use LiveTennisApi\LiveTennisApi;
use LiveTennisApi\Model\Analysis;
use LiveTennisApi\Model\Market;
use LiveTennisApi\Model\Page;
use LiveTennisApi\Model\Player;
use LiveTennisApi\Model\Score;
use LiveTennisApi\Model\TennisMatch;

/**
 * Facade for the configured {@see LiveTennisApi} client.
 *
 *     use LiveTennisApi\Laravel\Facades\LiveTennis;
 *
 *     $live = LiveTennis::listMatches('live');
 *
 * @method static array               health()
 * @method static Page                listMatches(string $status = 'live', ?string $tour = null, int $limit = 50, int $offset = 0)
 * @method static ?TennisMatch        getMatch(int $matchId)
 * @method static ?Score              getMatchScore(int $matchId)
 * @method static Page                listMatchEvents(int $matchId, int $limit = 50, int $offset = 0)
 * @method static ?Analysis           getMatchAnalysis(int $matchId)
 * @method static Page                searchPlayers(?string $search = null, int $limit = 50, int $offset = 0)
 * @method static ?Player             getPlayer(int $playerId)
 * @method static Page                listMarkets(int $matchId)
 * @method static ?Market             getMarketPrices(int $matchId, int $limit = 50)
 * @method static Page                listCompletedMatches(int $limit = 50, int $offset = 0)
 * @method static Page                listFixtures(?string $tour = null, int $limit = 50, int $offset = 0)
 *
 * @see LiveTennisApi
 */
final class LiveTennis extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LiveTennisApi::class;
    }
}
