# Live Tennis API — Laravel

Laravel integration for the [Live Tennis API](https://livetennisapi.com) — live
scores, fixtures, players, rankings, head-to-head and a 1968–2022 results
archive for **ATP, WTA, Challenger, ITF and juniors** — wrapping the official
[PHP client](https://github.com/livetennisapi/livetennisapi-php) with an
auto-discovered service provider, a `LiveTennis` facade, and a server-rendered
`<x-tennis-scores />` Blade component.

[![ci](https://github.com/livetennisapi/livetennisapi-laravel/actions/workflows/ci.yml/badge.svg)](https://github.com/livetennisapi/livetennisapi-laravel/actions/workflows/ci.yml)
[![Packagist](https://img.shields.io/packagist/v/livetennisapi/livetennisapi-laravel.svg)](https://packagist.org/packages/livetennisapi/livetennisapi-laravel)
[![license](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

[**Documentation**](https://docs.livetennisapi.com) · [**Get a free API key**](https://livetennisapi.com/subscribe/free)

## Install

```bash
composer require livetennisapi/livetennisapi-laravel
php artisan vendor:publish --tag=livetennis-config
```

Set your key in `.env`:

```dotenv
LIVE_TENNIS_API_KEY=twjp_your_key_here
```

The service provider is auto-discovered (`extra.laravel`), so there is nothing to
register manually.

## Facade

```php
use LiveTennisApi\Laravel\Facades\LiveTennis;

$live = LiveTennis::listMatches('live');
$player = LiveTennis::getPlayer(1218);
$fixtures = LiveTennis::listFixtures(tour: 'atp');

// Cross-era head-to-head: results archive + our own completed matches. BASIC.
$h2h = LiveTennis::getHeadToHead('federer', 'nadal');

// Point-in-time rankings. Listing mode (PRO); per-player as-of mode (ULTRA).
$table = LiveTennis::listRankings(systems: 'atp', limit: 100);

// The point-by-point tape for one match — works on a LIVE match too. BASIC.
$tape = LiveTennis::getHistoryMatch(18953, sequence: 'clean');
```

The facade proxies the configured `LiveTennisApi` singleton, so every client
method and its return types (`Page`, `TennisMatch`, `Player`, `Score`, …) are
available. See the PHP client README for the full method and error reference.

You can also type-hint the client directly anywhere the container resolves:

```php
public function __construct(private \LiveTennisApi\LiveTennisApi $tennis) {}
```

## Blade component

```blade
<x-tennis-scores />                                  {{-- config defaults --}}
<x-tennis-scores status="upcoming" :limit="5" tour="atp" />
```

Fetched and rendered **server-side** at render time. Any API or transport failure
is caught and rendered as a graceful empty/error state — a live-scores widget
never fatals the page. Publish and restyle the markup with:

```bash
php artisan vendor:publish --tag=livetennis-views
```

Attributes: `status` (`live` | `upcoming` | `completed`), `limit`, `tour`
(`atp` | `wta` | `challenger` | `itf` | `juniors`). Omitted values fall back to
`config('livetennis.scores.*')`.

**Tier note:** `status="live"` and `status="upcoming"` work on the free tier.
`status="completed"` lists completed matches, which the API gates behind the
BASIC tier ($9.99/mo) or any History plan — on a free key the component renders
a visible upgrade notice (with a link to
<https://livetennisapi.com/subscribe/upgrade>) instead of scores.

## Endpoints and tiers

| Facade methods | FREE | BASIC | PRO | ULTRA |
|---|:--:|:--:|:--:|:--:|
| `listMatches` `getMatch` `getMatchScore` | ✅ | ✅ | ✅ | ✅ |
| `searchPlayers` `getPlayer` `listFixtures` | ✅ | ✅ | ✅ | ✅ |
| `listTournaments` `getTournament` `getUsage` | ✅ | ✅ | ✅ | ✅ |
| `listCompletedMatches` `getHistoryMatch` (history tape) | — | ✅¹ | ✅ | ✅ |
| `getHeadToHead` · `listArchiveMatches` `getArchiveMatch` `listArchivePlayers` `getArchiveCareer` (1968–2022 results archive) | — | ✅¹ | ✅ | ✅ |
| `listMatchEvents` `listMarkets` `getMarketPrices` `listMatchPrices` | — | — | ✅ | ✅ |
| `listRankings` (rank-ordered listing) | — | — | ✅ | ✅ |
| `listHistoryPackages` `getHistoryPackage` (bulk downloads)² | — | — | ✅ | ✅ |
| `listRankings` (per-player as-of records) | — | — | — | ✅ |
| `getMatchStatistics` (in-play statistics) | — | — | — | ✅ |
| `listRallyMatches` `getRallyMatch` `getMatchRally` `getChartingPlayer` `getChartingMatch` (shot-by-shot) | — | — | — | ✅ |
| `getMatchAnalysis` · `getWsToken` (push feed) | — | — | — | ✅ |
| `createWebhook` `listWebhooks` `deleteWebhook` (direct keys only, max 3/key) | — | — | — | ✅ |

¹ Also unlocked by any History plan, which works on top of a FREE key.
² `kind: 'rankings'` packages and the `year` archive listing need ULTRA (the
`year` listing is also unlocked by History Business or a 1-year package).

List endpoints paginate with `limit` (≤200, default 50) and `offset`, and accept
the new filters where documented: `player` (repeatable), `from`/`to`, `country`
and `tour` (`atp | wta | challenger | itf | juniors`). Unknown filter values are
rejected with a 400, never silently ignored.

## Quotas

| Tier | Requests/min | Requests/day | Price |
|---|--:|--:|--:|
| FREE | 30 | 100 | $0 |
| BASIC | 60 | 1,000 | $9.99/mo |
| PRO | 300 | 10,000 | $29.99/mo |
| ULTRA | 600 | 500,000 | $99.99/mo |

At 100/day, a free key polling faster than every ~15 minutes will spend its
allowance before the day ends — an always-on `<x-tennis-scores />` page belongs
on BASIC. Every response carries `X-RateLimit-Limit` / `-Remaining` / `-Reset`
headers; a 429 carries `Retry-After`, and the client retries per-minute 429s
for you. Calling above your tier throws
`LiveTennisApi\Exception\UpgradeRequired`, which names the tier you need.

## Authentication

The client sends `Authorization: Bearer twjp_…` by default (preferred). Set
`LIVE_TENNIS_API_AUTH_HEADER=x-api-key` to send `X-API-Key` instead. Keep keys
in `.env` — never in Blade templates or anything that reaches the browser.

## Configuration (`config/livetennis.php`)

| Key | Env | Default |
| --- | --- | --- |
| `key` | `LIVE_TENNIS_API_KEY` | — |
| `base_url` | `LIVE_TENNIS_API_BASE_URL` | production base |
| `auth_header` | `LIVE_TENNIS_API_AUTH_HEADER` | `bearer` |
| `timeout` | `LIVE_TENNIS_API_TIMEOUT` | `30` |
| `max_retries` | `LIVE_TENNIS_API_MAX_RETRIES` | `2` |
| `scores.status` / `scores.limit` | — | `live` / `10` |

## Testing

```bash
composer install
vendor/bin/phpunit                       # offline, via orchestra/testbench
LIVETENNISAPI_KEY=twjp_… vendor/bin/phpunit --filter LiveSmokeTest   # live
```

## Requires

PHP 8.2+, Laravel 11 or 12, `livetennisapi/livetennisapi` ^1.1.

## Links

- Documentation: <https://docs.livetennisapi.com>
- Free API key: <https://livetennisapi.com/subscribe/free>
- Discord: <https://discord.gg/f8WUZHgDm6>
- GitHub org: <https://github.com/livetennisapi>

## Affiliate program

Know developers who need tennis data? The [affiliate program](https://affiliates.livetennisapi.com/program) pays 51% recurring commission for the life of every referred subscription — 30-day cookie, and the people you refer get 10% off.

## License

MIT.
