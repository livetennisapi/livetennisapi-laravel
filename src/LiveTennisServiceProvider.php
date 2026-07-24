<?php

declare(strict_types=1);

namespace LiveTennisApi\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use LiveTennisApi\Laravel\View\Components\TennisScores;
use LiveTennisApi\LiveTennisApi;

/**
 * Wires the Live Tennis PHP client into a Laravel application:
 *
 *  - binds a configured {@see LiveTennisApi} singleton (resolved lazily, so the
 *    PSR-18 transport is only discovered when the client is first used);
 *  - publishes `config/livetennis.php`, reading `LIVE_TENNIS_API_KEY` from env;
 *  - registers the `<x-tennis-scores />` Blade component and its view.
 *
 * Auto-discovered via `extra.laravel.providers` — no manual registration needed.
 */
final class LiveTennisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/livetennis.php', 'livetennis');

        $this->app->singleton(LiveTennisApi::class, static function (Application $app): LiveTennisApi {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('livetennis', []);

            $key = $config['key'] ?? null;

            return new LiveTennisApi(
                is_string($key) ? $key : null,
                [
                    'base_url' => $config['base_url'] ?? LiveTennisApi::DEFAULT_BASE_URL,
                    'auth_header' => $config['auth_header'] ?? 'bearer',
                    'timeout' => (float) ($config['timeout'] ?? LiveTennisApi::DEFAULT_TIMEOUT),
                    'max_retries' => (int) ($config['max_retries'] ?? LiveTennisApi::DEFAULT_MAX_RETRIES),
                ],
            );
        });

        // Facade accessor.
        $this->app->alias(LiveTennisApi::class, 'livetennis');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livetennis');

        Blade::component('tennis-scores', TennisScores::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/livetennis.php' => $this->app->configPath('livetennis.php'),
            ], 'livetennis-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => $this->app->resourcePath('views/vendor/livetennis'),
            ], 'livetennis-views');
        }
    }
}
