<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel\Tests;

use GuzzleHttp\Psr7\Response;
use LiveTennisApi\Laravel\Facades\LiveTennis;
use LiveTennisApi\Model\H2HMeeting;
use LiveTennisApi\Model\HeadToHead;
use LiveTennisApi\Model\HistoryTape;
use LiveTennisApi\Model\HistoryTapeRow;
use LiveTennisApi\Model\Page;
use LiveTennisApi\Model\RankingListMeta;
use LiveTennisApi\Model\RankingRecord;
use LiveTennisApi\Model\WsToken;

/**
 * Facade proxying for the client's 1.1 surface — head-to-head, the tape,
 * rankings and the push-feed token all decode into their models through the
 * container-bound singleton, exactly as the direct client does.
 */
final class FacadeNewSurfaceTest extends TestCase
{
    public function testHeadToHeadDecodesAcrossEras(): void
    {
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'players' => ['p1' => ['name' => 'Novak Djokovic'], 'p2' => ['name' => 'Rafael Nadal']],
                'totals' => ['p1_wins' => 31, 'p2_wins' => 29, 'meetings' => 61, 'undecided' => 1],
                'by_surface' => ['clay' => ['p1' => 9, 'p2' => 20]],
                'meetings' => [
                    ['era' => 'current', 'date' => '2024-07-29', 'winner' => 1, 'outcome' => 'completed', 'match_id' => 18754],
                    ['era' => 'archive', 'date' => '2022-05-22', 'winner' => 2, 'outcome' => 'completed', 'archive_match_id' => 1387211],
                ],
            ])),
        ]);

        $h2h = LiveTennis::getHeadToHead('djokovic', 'nadal');

        $this->assertInstanceOf(HeadToHead::class, $h2h);
        $this->assertSame(31, $h2h->totals['p1_wins']);
        $this->assertSame(20, $h2h->by_surface['clay']['p2']);
        $this->assertCount(2, $h2h->meetings);
        $this->assertInstanceOf(H2HMeeting::class, $h2h->meetings[0]);
        $this->assertSame('current', $h2h->meetings[0]->era);
        $this->assertSame('archive', $h2h->meetings[1]->era);
    }

    public function testRankingsListingModeWithMovementAndCoverage(): void
    {
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    ['player_id' => 3819, 'player_name' => 'Jannik Sinner', 'system' => 'atp', 'rank' => 1, 'points' => 11480, 'previous_rank' => 1],
                    ['player_id' => null, 'player_name' => 'Botic van de Zandschulp', 'system' => 'atp', 'rank' => 87, 'points' => 673, 'previous_rank' => null],
                ],
                'meta' => [
                    'limit' => 50, 'offset' => 0, 'count' => 2, 'total' => 2214, 'has_more' => true,
                    'coverage' => ['systems_resolved' => ['atp']],
                ],
            ])),
        ]);

        $page = LiveTennis::listRankings(systems: 'atp');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertInstanceOf(RankingRecord::class, $page[0]);
        $this->assertSame(1, $page[0]->previous_rank);
        // listing rows outside our roster keep the published name, id null
        $this->assertNull($page[1]->player_id);
        $this->assertSame('Botic van de Zandschulp', $page[1]->player_name);
        $this->assertInstanceOf(RankingListMeta::class, $page->meta);
        $this->assertSame(['atp'], $page->meta->coverage['systems_resolved']);
    }

    public function testHistoryTapeCleanSequenceCarriesPointWinner(): void
    {
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'match' => ['id' => 21980, 'tournament' => 'Cincinnati Open', 'status' => 'completed', 'winner' => 1],
                'tape' => [
                    ['sets' => [0, 0], 'points' => ['0', '0'], 'server' => 1, 'win_probability_p1' => 0.71, 'point_winner' => null],
                    ['sets' => [0, 0], 'points' => ['15', '0'], 'server' => 1, 'win_probability_p1' => 0.72, 'point_winner' => 1],
                ],
                'meta' => ['sequence' => 'clean', 'coverage' => 'point'],
            ])),
        ]);

        $tape = LiveTennis::getHistoryMatch(21980, sequence: 'clean');

        $this->assertInstanceOf(HistoryTape::class, $tape);
        $this->assertSame('Cincinnati Open', $tape->match->tournament);
        $this->assertCount(2, $tape->tape);
        $this->assertInstanceOf(HistoryTapeRow::class, $tape->tape[1]);
        $this->assertSame(1, $tape->tape[1]->point_winner);
        $this->assertSame(0.72, $tape->tape[1]->win_probability_p1);
    }

    public function testWsTokenMintsPushFeedCredentials(): void
    {
        $this->bindFakeClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'token' => 'eyJ.fixture.sig',
                'expires_in' => 300,
                'ws_url' => 'wss://api.livetennisapi.com/connection/websocket',
                'channels' => ['match' => 'match:{id}', 'slate' => 'slate:all'],
            ])),
        ]);

        $ws = LiveTennis::getWsToken();

        $this->assertInstanceOf(WsToken::class, $ws);
        $this->assertSame(300, $ws->expires_in);
        $this->assertSame('wss://api.livetennisapi.com/connection/websocket', $ws->ws_url);
        $this->assertSame('slate:all', $ws->channels['slate']);
    }
}
