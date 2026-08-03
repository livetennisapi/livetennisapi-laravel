# Live Tennis API — Laravel

Laravel integration for the [Live Tennis API](https://livetennisapi.com), wrapping the
official [PHP client](https://github.com/livetennisapi/livetennisapi-php): an
auto-discovered service provider, a `LiveTennis` facade, and a server-rendered
`<x-tennis-scores />` Blade component.

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

PHP 8.2+, Laravel 11 or 12.

## Affiliate program

Know developers who need tennis data? The [affiliate program](https://affiliates.livetennisapi.com/program) pays 51% recurring commission for the life of every referred subscription — 30-day cookie, and the people you refer get 10% off.

## License

MIT.
