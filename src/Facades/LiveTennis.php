<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use LiveTennisApi\LiveTennisApi;
use LiveTennisApi\Model\Analysis;
use LiveTennisApi\Model\ArchiveCareer;
use LiveTennisApi\Model\ArchiveMatch;
use LiveTennisApi\Model\ChartingMatch;
use LiveTennisApi\Model\ChartingPlayer;
use LiveTennisApi\Model\HeadToHead;
use LiveTennisApi\Model\HistoryPackage;
use LiveTennisApi\Model\HistoryTape;
use LiveTennisApi\Model\Market;
use LiveTennisApi\Model\MatchStatistics;
use LiveTennisApi\Model\Page;
use LiveTennisApi\Model\Player;
use LiveTennisApi\Model\RallyMatch;
use LiveTennisApi\Model\Score;
use LiveTennisApi\Model\TennisMatch;
use LiveTennisApi\Model\Tournament;
use LiveTennisApi\Model\Usage;
use LiveTennisApi\Model\Webhook;
use LiveTennisApi\Model\WsToken;

/**
 * Facade for the configured {@see LiveTennisApi} client.
 *
 *     use LiveTennisApi\Laravel\Facades\LiveTennis;
 *
 *     $live = LiveTennis::listMatches('live');
 *
 * @method static array               health()
 * @method static Page                listMatches(string $status = 'live', ?string $tour = null, int $limit = 50, int $offset = 0, array $filters = [])
 * @method static ?TennisMatch        getMatch(int $matchId)
 * @method static ?Score              getMatchScore(int $matchId)
 * @method static Page                listMatchEvents(int $matchId, int $limit = 50, int $offset = 0)
 * @method static ?Analysis           getMatchAnalysis(int $matchId)
 * @method static ?MatchStatistics    getMatchStatistics(int $matchId)
 * @method static Page                searchPlayers(?string $search = null, int $limit = 50, int $offset = 0)
 * @method static ?Player             getPlayer(int $playerId)
 * @method static Page                listMarkets(int $matchId)
 * @method static ?Market             getMarketPrices(int $matchId, int $limit = 50)
 * @method static Page                listMatchPrices(int $matchId, ?int $limit = null, ?int $minutes = null)
 * @method static Page                listCompletedMatches(int $limit = 50, int $offset = 0, array $filters = [])
 * @method static Page                listFixtures(?string $tour = null, int $limit = 50, int $offset = 0)
 * @method static Page                listTournaments(?string $search = null, ?string $tour = null, int $limit = 50, int $offset = 0)
 * @method static ?Tournament         getTournament(string $tournamentId)
 * @method static ?Usage              getUsage()
 * @method static ?HistoryTape        getHistoryMatch(int $matchId, ?string $sequence = null)
 * @method static ?HeadToHead         getHeadToHead(string $p1, string $p2)
 * @method static Page                listArchiveMatches(array $filters = [], int $limit = 50, int $offset = 0)
 * @method static ?ArchiveMatch       getArchiveMatch(int $archiveId)
 * @method static Page                listArchivePlayers(array $filters = [], int $limit = 50, int $offset = 0)
 * @method static ?ArchiveCareer      getArchiveCareer(string $name)
 * @method static Page                listRankings(array $players = [], ?string $asOf = null, string|array|null $systems = null, int $limit = 50, int $offset = 0)
 * @method static Page                listRallyMatches(array $filters = [], int $limit = 50, int $offset = 0)
 * @method static ?RallyMatch         getRallyMatch(int $rallyMatchId, int $limit = 50, int $offset = 0)
 * @method static ?RallyMatch         getMatchRally(int $matchId, int $limit = 50, int $offset = 0)
 * @method static ?ChartingPlayer     getChartingPlayer(string $name, ?string $gender = null)
 * @method static ?ChartingMatch      getChartingMatch(int $chartingMatchId)
 * @method static ?WsToken            getWsToken()
 * @method static Page                listHistoryPackages(?string $kind = null, ?string $year = null)
 * @method static ?HistoryPackage     getHistoryPackage(string $period, ?string $kind = null)
 * @method static ?Webhook            createWebhook(string $url, array $events = ['score'])
 * @method static Page                listWebhooks()
 * @method static bool                deleteWebhook(int $webhookId)
 * @method static \Generator          paginate(string $method, array $args = [], int $pageSize = LiveTennisApi::MAX_LIMIT, array $extraArgs = [])
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
