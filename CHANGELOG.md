# Changelog

All notable changes to `livetennisapi/livetennisapi-laravel` are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/);
versions follow [SemVer](https://semver.org).

## [1.1.0] — 2026-08-07

### Added

- Facade coverage for the PHP client's 1.1 surface: tournaments, the
  point-by-point tape (`getMatchTape`), in-play statistics, cross-era
  head-to-head (`getH2H`), the 1968–2022 results archive (matches, players,
  career aggregates), point-in-time rankings (listing = PRO, per-player
  as-of = ULTRA), shot-by-shot rally/charting reads, bulk history packages,
  `/usage`, and `getWsToken()` for the Centrifugo push feed.
- New list filters proxied through the facade: `player` (repeatable),
  `from`/`to`, `country` and `tour` (`atp | wta | challenger | itf | juniors`).
- `scripts/truthcheck.sh` plus a CI step that pins README/product copy to
  ground truth (quotas, docs URL, reset wording).
- CHANGELOG (this file).

### Changed

- Requires `livetennisapi/livetennisapi` ^1.1.
- README rebuilt to the fleet standard: tier-gated endpoint table, quota grid
  (2026-08-06 change — FREE 100/day, BASIC 1,000/day, PRO 10,000/day,
  ULTRA 500,000/day), auth section and links block.

## [1.0.1] — 2026-08-02

### Added

- Visible upgrade notice in `<x-tennis-scores />` when `status="completed"` is
  gated (403 `upgrade_required`), instead of the generic error fallback.

### Changed

- Pinned `livetennisapi/livetennisapi` to ^1.0.

## [1.0.0] — 2026-07-24

### Added

- Initial release: auto-discovered service provider binding a configured
  `LiveTennisApi` singleton, the `LiveTennis` facade, publishable
  `config/livetennis.php`, and the server-rendered `<x-tennis-scores />`
  Blade component.
